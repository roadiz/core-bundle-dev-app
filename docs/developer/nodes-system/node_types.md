# Managing node-types

First and foremost, you need to create a new node-type before creating any kind of node.

If you want to know more about what a node-type is, please visit the other section of the developer documentation.

## Add node-type

To add a new node type, follow these steps:

1. **Create the YAML file**  
   In the `config/node_types/` directory, create a file named after your node type in lowercase (e.g., `nodetypename.yaml`).

2. **Follow the required structure**  
   The file must follow the correct structure and parameter types:

```yaml
   # 'name' is a required string.
   name: nodeTypeName

   # 'displayName' is a required string.
   displayName: 'The node type display name for editors'

   # 'color' is an optional string.
   color: '#000000'

   # 'description' is an optional string.
   description: nodeTypeName description

   # 'visible' is an optional boolean.
   visible: true

   # 'publishable' is an optional boolean.
   publishable: false

   # 'attributable' is an optional boolean.
   attributable: true

   # 'sortingAttributesByWeight' is an optional boolean.
   sortingAttributesByWeight: false

   # 'reachable' is an optional boolean.
   reachable: true

   # 'hidingNodes' is an optional boolean.
   hidingNodes: false

   # 'hidingNonReachableNodes' is an optional boolean.
   hidingNonReachableNodes: true

   # 'fields' defines the list of fields for the node type.
   # This section will be explained below in "Adding node-type field"
   fields:
       - ...
       - ...

   # 'defaultTtl' is an optional integer. It represents the number of minutes node' responses will live in HTTP cache
   defaultTtl: 15

   # 'searchable' is an optional boolean.
   searchable: true
```

::: tip  
You can use the `nodetypes:validate-files` command to check if your file structure is correct.
:::

Once validated, run the `app:migrate` command and verify:

1. The `src/GeneratedEntity/NSNodeTypeName.php` file was correctly generated.
2. The API configuration in `config/api_resources/nsnodetypename.yml` is correct (you can test it via `{{url}}/api/docs`).

## Delete node-type

To delete a node type, remove the associated files from the following directories:

- `config/node_types/`
- `src/GeneratedEntity/`
- `config/api_resources/`

::: warning
Deleting a node type **does not** remove the nodes linked to that type in the database.  
You need to create a migration (command: `bin/console doctrine:migrations:generate`) and choose one of the following solutions.
:::

## Soft Delete

If you want to transfer nodes to another existing node type while keeping their data, use a migration like this:

```php
public function up(Schema $schema): void
{
    $this->addSql("UPDATE nodes SET nodetype_name = 'AnotherNodeTypeName' WHERE nodetype_name = 'NodeTypeName'");
    $this->addSql("UPDATE nodes_sources SET discr = 'AnotherNodeTypeName' WHERE discr = 'NodeTypeName'");
}

public function down(Schema $schema): void
{
    // Leave empty
}
```

::: tip  
Alternatively, if you want to keep your data without transferring it to another node type, you can create a "ghost" node type (`GhostNodeType`) with property visible to `false` and has no fields, then transfer your nodes there.
:::

## Hard Delete

To completely delete all nodes (and children) associated with the node type, use a migration like this:

::: warning  
This method will delete all node and also its children in cascade.
:::

```php
public function up(Schema $schema): void
{
    $this->addSql("DELETE FROM nodes WHERE nodetype_name = 'NodeTypeName'");
    $this->addSql("DELETE FROM nodes_sources WHERE discr = 'NodeTypeName'");
}

public function down(Schema $schema): void
{
    // Leave empty
}
```

## Adding node-type field

To add fields to a node type, modify the `fields` property in your YAML file.  
For example:

```yaml
fields:
    -
        # Example field with minimal requirements
        name: field_name_1
        label: Field Name
        type: string
    -
        # Example field with all possible parameters
        name: field_name_2
        label: Field Name Two
        type: markdown
        groupName: string
        placeholder: string
        description: string
        minLength: 0
        maxLength: 50
        serializationMaxDepth: 2
        universal: false
        excludeFromSearch: false
        excludedFromSerialization: false
        indexed: false
        visible: true
        expanded: false
        defaultValues: null  # depends on the type
        normalizationContext:
            groups:
                - get
                - nodes_sources_base
                - nodes_sources_default
        serializationGroups: null
        serializationExclusionExpression: null
```

For more details on field types and parameters, refer to nodes-type-fields.

::: tip
 Always validate your file with `nodetypes:validate-files` before running `app:migrate`.  
 This command will:

 - Update your node source entity.
 - Generate a migration to add your fields to `node_sources` database table if they do not already exist in another node type.
:::

## Removing node-type field

To remove a field from a node type, open the YAML file in `config/node_types/`  
and delete the corresponding field from the `fields` array.

**Example:**

Before (removing `field_name_2`):

```yaml
fields:
    -
        name: field_name_1
        label: Field Name
        type: string
    -
        name: field_name_2
        label: Field Name Two
        type: markdown
```

After:

```yaml
fields:
    -
        name: field_name_1
        label: Field Name
        type: string
```

::: tip  
As with adding fields, validate your file with `nodetypes:validate-files` and then run `app:migrate`.  
This command will update your node source entity and generate a migration to drop the field from `node_sources` if it is not used by another node type.
:::