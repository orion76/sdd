# Taxonomy Place

This module dynamically creates a 'Place' vocabulary using Address module to
 manage the geographical information. The taxonomy terms that are created are
 nested by country, then state/province, then city. The Address module is used
 as an API to retrieve the right country and province codes and names, and to
 manage the nesting of countries, provinces, and localities.

Instead of creating a huge taxonomy of all possible locations, many of which
 may never be used, the module creates the taxonomy as it is required. For
 instance, users fill out an address field on a node to identify the place the
 node should be associated with. When the node is saved, the node's
 entity_reference field is updated to link it to the correct place taxonomy
 term, adding it to the vocabulary, if it doesn't already exist. In this way,
 the vocabulary only contains places that have actually been referenced.

The module creates new terms if they don't exist, but  does NOT delete terms
 when the references are removed, since the terms may have been updated to
 include other information and might be used again in the future.

## Fields on the taxonomy term

Create a vocabulary for the places and add fields to it:

- Required: Address field (address), a field to contain the full address for
 this place.
- Required: Short name field (text) i.e. 'Chicago', the place name, to be used
 where the full name of the place is too long.
- Required: Sortable name (text) i.e. 'US/Illinois/Chicago', a string value
 that represents the nested structure of the place names, which can be used in
 views to ensure the places are sorted correctly without any need for
 complicated joins.
- Optional: Description field (long text).
- Optional: Geolocation or geofield, a field to contain the geocoordinates for
 this place to be displayed as a map on the term page.

The description field will be enhanced if the
 [Wikipedia Client](https://www.drupal.org/project/wikipedia_client) module is
 enabled. If that module exists, the description field will be populated with
 a description of the place pulled from Wikipedia.

Display a map on the place term by enabling the
 [Geolocation](https://www.drupal.org/project/geolocation) and
 [Geolocation Address Link](https://www.drupal.org/project/geolocation_address_link)
 modules. Add a Geolocation field to the taxonomy term, and configure it to be
 geocoded
 from the address field data when the term is created. You can do something
 similar using the [Geofield](https://www.drupal.org/project/geofield)
  and [Geocoder](https://www.drupal.org/project/geocoder) modules.

## Referencing content fields

### Node field configuration

Each content type that references the Place taxonomy needs two special fields:

- One should be the usual entity_reference field to link the node to places in
 the Place taxonomy.
- The other should be an address field to allow the user to identify a place,
 even if it is not already in the Place taxonomy.

### Node form display

Hide the entity_reference field and display the address field.

This module will convert that address into a link to the correct taxonomy term.

### Node view display
Hide the address field and display the entity reference field instead.


## Taxonomy Place settings

Once the vocabulary and fields have been created, go to the confiration form
 (/admin/config/system/taxonomy-place/settings) to identify the vocabulary and
 the required fields on the taxonomy term needed to store the Place
 information, and map the address and entity_reference fields on the referencing
 content that will be used to create and update the terms.

## Address field configuration

The module takes advantage of the ability to use optional and hidden address
 fields so users can select only a country, or only a country and
 state/province, while omitting values that only make sense in postal addresses,
 like postal codes.

Configure the address fields on both the taxonomy term and the referencing
 content this way:

- Country: required
- Administrative area: optional
- Locality: optional
- Organiation name: either hidden or optional (if used to hold a Place name)
- All other address fields: hidden
