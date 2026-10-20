---
status: proposed
---

# Split File from Document

Tracked in [#517](https://github.com/roadiz/core-bundle-dev-app/issues/517).

A `Document` currently is both the editorial object (translations, folders, copyright, hotspot, usages in node-sources, tags, attributes and custom forms) and one binary (filename, folder, MIME type, size, hash, dimensions, duration). Every "several binaries for one content" need was therefore modelled as extra `Document` rows linked in ad-hoc ways:

| Need | Current link | Problem |
|---|---|---|
| Alternative encodings (`mp4`/`webm`, `mp3`/`ogg`) | Same filename basename, any folder | Unrelated uploads sharing a filename are linked; no editor control |
| Video poster | Same filename basename, image extension | Same as above |
| Thumbnail of a video, PDF or embed | `Document::$original` | Thumbnail is a full `Document`, with its own translations and listing in the explorer |
| Original upload before downscale | `Document::$rawDocument` + `raw` flag | A fake `Document` every query must exclude (`d.raw = false`) |

We decided that a **Document** owns zero or more **Files**, each with a **role**: `main`, `source`, `poster`, `thumbnail` or `raw`. A File belongs to exactly one Document. An embed Document has no `main` File.

## Decisions

- **One owner per File.** No File is shared between Documents: deleting a Document deletes its Files and binaries, with no reference counting.
- **Editorial data stays on Document**: translations, folders, copyright dates, hotspot and crop alignment, embed platform and id, `private` flag. A private Document keeps all its Files in private storage.
- **Binary data moves to File**: filename, storage folder, MIME type, size, hash and algorithm, image dimensions, average color, media duration.
- **Storage paths do not change.** A File keeps the storage folder of the Document row it comes from, so public URLs and Intervention Request paths stay valid.
- **The API keeps its flat shape.** `Document` and `DocumentDto` output still expose the `main` File fields at root (`relativePath`, `mimeType`, `imageWidth`…). `altSources` and `thumbnail` are computed from roles instead of filename lookups. `DocumentInterface` getters delegate to the `main` File for a deprecation period.
- **Explicit links migrate automatically, heuristic links do not.** A Doctrine migration converts `rawDocument` and `original` links into Files, since they are explicit. Filename-based sources and posters are converted by a console command with a dry-run report, so that ambiguous groups (same basename in several folders) can be reviewed before they are linked. Until the command runs, the filename lookup stays as a deprecated fallback.

## Considered Options

- **Keep one binary per Document, add an explicit `Document`↔`Document` "source of" relation.** Smaller change, but keeps duplicated translations, keeps `raw` rows everywhere and still needs one relation type per need.
- **Many-to-many Files shared between Documents.** Allows reusing a binary, but brings reference counting on delete and unclear ownership of private storage. No current need.
- **Migrate filename-based groups automatically in the Doctrine migration.** Rejected: migrations run unattended in production, and the filename heuristic is exactly what links unrelated documents today.

## Consequences

- Every `DocumentRepository` query and `DocumentDto` select joins the `main` File: one more join on the hottest API path.
- Document factories (upload, embed, download), downscaling, private/public moves, duplicate detection by hash, Rozier explorer and document edit screens must work on Files.
- Solr indexing stays on Document translations: its only binary fields (`filename_s`, `mime_type_s`) come from the `main` File through `DocumentInterface` getters. Thumbnails and `raw` rows are no longer Documents, so they can no longer be indexed.
- `lib/Documents` (separately published) needs a File model and interface; `DocumentFinderInterface` filename methods become deprecated.
- TypeScript definitions (`DtsGenerator`) must be regenerated if new fields like `files` are exposed.
