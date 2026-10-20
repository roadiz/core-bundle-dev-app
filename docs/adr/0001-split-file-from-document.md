---
status: proposed
---

# Split File from Document

Tracked in [#517](https://github.com/roadiz/core-bundle-dev-app/issues/517).

A `Document` currently is both the editorial object (translations, folders, copyright, hotspot, usages in node-sources, tags, attributes and custom forms) and one binary (filename, folder, MIME type, size, hash, dimensions, duration). Every "several binaries for one content" need was therefore modelled as extra `Document` rows linked in ad-hoc ways:

| Need | Current link | Problem |
|---|---|---|
| Alternative encodings (`mp4`/`webm`, `mp3`/`ogg`) | Same filename basename, any folder | Unrelated uploads sharing a filename are linked; no editor control |
| Video poster | First thumbnail, then same filename basename with an image extension | Same as above for the fallback |
| Thumbnail of a video, PDF or embed | `Document::$original` | Thumbnail is a full `Document`, with its own translations and listing in the explorer |
| Original upload before downscale | `Document::$rawDocument` + `raw` flag | A fake `Document` every query must exclude (`d.raw = false`) |

We decided that a **Document** owns zero or more **Files**, each with a **role**. A File belongs to exactly one Document.

| Role | Cardinality | Use |
|---|---|---|
| `main` | 0..1 | The file the Document stands for. None for an embed |
| `source` | 0..n, ordered | Alternative encodings of the `main` file, rendered as `<source>` in their position order, since browsers play the first supported one |
| `thumbnail` | 0..1 | Preview of a video, audio, PDF or embed, also used as video poster |
| `raw` | 0..1 | Original upload kept before downscaling |

Poster and thumbnail are one role: `VideoRenderer` already uses the first thumbnail as poster, and every renderer only reads the first thumbnail.

## Decisions

- **One owner per File.** No File is shared between Documents: deleting a Document deletes its Files and binaries, with no reference counting.
- **Cardinality is enforced by the Document API**, not by the database (MySQL and MariaDB have no partial unique index): `setMainFile()`, `setThumbnailFile()` and `setRawFile()` replace, `addSource()` / `removeSource()` manage the ordered list. There is no generic `addFile($role)`.
- **Editorial data stays on Document**: translations, folders, copyright dates, hotspot and crop alignment, embed platform and id, `private` flag.
- **Binary data moves to File**: filename, storage folder, MIME type, size, hash and algorithm, image dimensions, average color, media duration.
- **A File reads `private` from its Document** to resolve its mount path (public or private storage). The single owner makes this unambiguous, and nothing is denormalized.
- **Storage paths do not change.** A File keeps the storage folder of the Document row it comes from, so public URLs and Intervention Request paths stay valid.
- **The API keeps its flat shape.** `Document` and `DocumentDto` output still expose the `main` File fields at root (`relativePath`, `mimeType`, `imageWidth`…). `altSources` and `thumbnail` are computed from roles instead of filename lookups.
- **Explicit links migrate automatically, heuristic links do not.** A Doctrine migration converts `rawDocument` and `original` links into Files, since they are explicit. Filename-based sources and posters are converted by a console command with a dry-run report, so that ambiguous groups (same basename in several folders) can be reviewed before they are linked. Until the command runs, the filename lookup stays as a deprecated fallback.

## Transition in `lib/Documents`

`lib/Documents` is used outside Roadiz, by projects that may keep a single entity for both document and file. The split happens at interface level first, without breaking them:

1. **2.7: extract interfaces, change no implementer.**
   - `FileInterface` holds the binary methods already declared in `BaseDocumentInterface` (filename, folder, MIME type, short type, `is{Image,Svg,Webp,Video,Audio,Pdf}()`, relative and mount paths, `isLocal()`, `isProcessable()`). `BaseDocumentInterface` extends it, so every current implementer (`Document`, `SimpleDocument`, `DocumentDto`, third-party entities) is a `FileInterface` without code change.
   - Hotspot and crop alignment move from `SizeableInterface` to their own interface, which `SizeableInterface` extends.
   - Consumers that only need binary data (renderers, URL generators, media finders, factories) type-hint `FileInterface`.
   - Alternative sources and thumbnail are read through a new provider interface taking a document, with a filename-based default implementation. `DocumentFinderInterface` is not extended, so its implementers do not break.
   - `UPGRADE.md` asks implementers to declare `FileInterface` explicitly: PHP cannot tell an inherited interface from a declared one, so no runtime deprecation is possible.
2. **2.8: Roadiz `Document` stops being a File.**
   - `BaseDocumentInterface` and `SizeableInterface` no longer extend `FileInterface` and the hotspot interface. A single-entity project keeps working by declaring `implements DocumentInterface, FileInterface`.
   - Roadiz `File` entity implements `FileInterface`. A single resolution point gives consumers a File from a Document: the document itself when it is a `FileInterface`, its `main` File otherwise.
   - Roadiz implements the sources provider with roles.

## Considered Options

- **Keep one binary per Document, add an explicit `Document`↔`Document` "source of" relation.** Smaller change, but keeps duplicated translations, keeps `raw` rows everywhere and still needs one relation type per need.
- **Many-to-many Files shared between Documents.** Allows reusing a binary, but brings reference counting on delete and unclear ownership of private storage. No current need.
- **Separate `poster` and `thumbnail` roles.** Rejected: current code already treats the first thumbnail as poster, and no renderer needs both.
- **Migrate filename-based groups automatically in the Doctrine migration.** Rejected: migrations run unattended in production, and the filename heuristic is exactly what links unrelated documents today.
- **Split interfaces directly in 2.8.** Rejected: projects using `lib/Documents` with a single entity would get a breaking change with no deprecation period.

## Consequences

- Every `DocumentRepository` query and `DocumentDto` select joins the `main` File: one more join on the hottest API path.
- Document factories (upload, embed, download), downscaling, private/public moves, duplicate detection by hash, Rozier explorer and document edit screens must work on Files.
- Solr indexing stays on Document translations: its only binary fields (`filename_s`, `mime_type_s`) come from the `main` File through `DocumentInterface` getters. Thumbnails and `raw` rows are no longer Documents, so they can no longer be indexed.
- `FileAwareInterface` already exists in `lib/Documents` with another meaning (storage paths provider): the "document having files" interface must not use the `FileAware` name.
- TypeScript definitions (`DtsGenerator`) must be regenerated if new fields like `files` are exposed.
