# Specific De/Normalizer Used For The API

First, this is a flowchart about De/Normalization in Symfony applications:

![Please have a look at the documentation below](https://symfony.com/doc/current/_images/serializer/serializer_workflow.svg)<br>
As seen [here](https://symfony.com/doc/current/serializer.html#the-serialization-process-normalizers-and-encoders)

## IRI Normalizer

- When returning an object from the API we add an `@iri` property to it so the client has the info for their request.
- When returning a list of objects, we add those same `@iri` to each of them, plus we add extra info:
  - Pagination information if relevant
  - Number of total object if relevant

```json 
{
  "total": "int",
  "prevPage": "/iri",
  "nextPage": "/iri",
  "collection": [
    { 
      "@iri": "/iri",
      "properties": "..."
    }
  ]
}
```

The Normalizer chain is:
 - Entrypoint: File Objects; FileObjectNormalizer to add contentUrl
 - Entrypoint: Bookmark Objects; BookmarkNormalizer to filter out private tags
 - Then: All Objects; IriNormalizer to add `@iri` on objects
 - Finally: serializer.normalizer.object to serialize the object

## IRI Denormalizer

- When the client send an object with relation to the API, it is required that those relation are valid `@iri`.

```json
{
  "property": "...",
  "mainImage": "/iri",
  "tags": [
    "/iri",
    "..."
  ]
}
```
