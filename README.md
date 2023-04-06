# Mugo DAM extension - mugo_dam

*mugo_dam* stands for Mugo Digital Asset Manager. It has 2 parts:

1. the Mugo DAM Server
2. and this eZ Publish extension (client side integration to access the DAM Server)

This eZ Publish extension comes with a new datatype for images. The datatype features an enhanced image upload interface supporting drag and drop of images. Images uploaded by the user do not get stored in the usual way eZ Publish stores content images; instead, images are stored on the Digital Asset Server. The Digital Asset Server is a separate service (idependent of eZ Publish) supporting image-uploading and image-serving in different formats (similar to eZ Publish's image aliases).

## Installation
* Create entry in site.ini to enable the extension: `ActiveExtensions[]=mugo_dam`
* Copy mugo_dam/settings/mugo_dam.ini to settings/override/
* Customize the settings file
* Clear cache
* Create new autoload file
* Add the new datatype to a content class

##The datatype
The eZ Publish datatype is called 'dam_images'. It is responsible to store the references to the images on the image server -- it is not storing the images locally. The datatype stores those references in an associated array:
```
array(
  'standard' => array(
      url => '/link/to/image.jpg',
      alt => 'Image alt text',
  ),
  'square' => array(
      url => '/link/to/square_image.jpg',
      alt => 'Image alt text for square image',
  ),
)
```

The array key is the ratio identifier 'standard' or 'square'. So a single attribute instance is able to store multiple images (that's different to the eZ Publish standard image datatype). Do not confuse 'ratio' with image aliases. Because each ratio has one original image the user uploads - and for each ratio image you can have multiple image aliases.
This associated array gets serialized and stored in the 'data_text' field in the database.
The image ratio identifiers are configured in the class definition. Each ratio identifier has options like 'required', 'alt. text required'.

##The attribute_view_gui template

In the templates you can use the attribute_view_gui to render an image. Following parameters are supported:

* image_alias
* image_ratio_identifier

Please write your own view template if you need a more complex solution to render img tags. An alternative is to use a template operator called 'image_url'. You'd execute the operator on a DAM image attribute and you have to option to specify 3 parameters:

* alias
* image_ratio_identifier
* protocol ('http', 'https', 'none', 'auto')

Here is an example:
```
{def $image_path = $attribute|image_url( 's300x200', 'standard', 'none' )}
```

It would produce a URL similar to '//images.domain.com/path/to/image.jpg?alias=s300x200

##Image aliases
The image server (not part of this extension) is responsible to generate the image aliases. That's why it's not required that you configure image alias definition in eZ Publish anymore. There is an exception, because if you allow embed images in the ezxmltext attribute, the UI allows you to pick which alias to use for the embedded image. The UI shows a dropdown box for those options and it reads the ezp settings for that. So, it's probably a good idea to configure the image alias definition both on the image server and in ezpublish.

##Class MugoDamFunctionCollection
This class is part of this extension, it contains some helper function to communicate with the image server. So if an editor uploads a new image in the admin UI, the datatype will use this class to upload (using curl) the image the the image server. On publish, the datatype will execute a 'rename' on the image server. It will rename the image on the image server, so that it contains the object name in the image file.

##Import data with the 'fromString' method
The datatype implements the fromString (and also toString) method. The string is the serialized from of the content in the DB field data_text. See more under 'The datatype' headline. The fromString method can handle remote image if you specify its URL.

##Useful functions in PHP

```
MugoDamFunctionCollection::uploadToDam( $imagePath, $fileName = null, $creationTime = null )
```
It will upload a given $imagePath (with optional fileName and creationTime) to the image server. The imagePath can be a local image or remote image specified as a URL.
  
```
dam_imagesType::getImageUrlByAttribute( $contentObjectAttribute, $alias = null, $image_ratio_identifier = 'standard', $protocol = 'none' )
```
Returns an image URL based on the given eZ Attribute, alias, ratio identifier and protocol.

##ezfind and mugo_dam
The extension comes with a handler class 'ezfSolrDocumentFieldDamImages' to implement to logic how the referenced images are index in solr.

##uploadHandler
eZ Publish allows to develop custom upload handlers. That handler is used in various editorial operations, for example if you upload an image in an exzmltext attribute, or if an editor uploads an image via webdav. mugo_dam comes with a custom uploadHandler which will make sure the image ends up on the image server.
In the settings file you specify the target content class (in most cases 'image' ) and the required attribute identifiers.

##Settings
All settings have inline documentation, have a look here:
https://github.com/mugoweb/mugo_dam/blob/master/settings/mugo_dam.ini

##Data migration
The extension contains a migration script "MugoMigrateImages" to move images from the standard ezp attribute to the new datatype attribute. That script is written as a 'Mugo Task' and you would need to [mugo_queue](https://github.com/mugoweb/mugo_queue) extension to execute it.
You need to edit that script to specify the mapping of old to new attributes.


## Performance
The way eZ Publish serves images has some downsides which the DAM Server addresses and solves:

1) The way eZ Publish serves images in a cluster setup is slow. It servers the images from a shared file system (like NFS) and keeps a reference for each image (and image alias) in a cluster database. You easily end up with millions of entries in that cluster database for bigger sites.
The Digital Asset Server improves the performance here: 

* it servers the images directly from the local disk
* it is a dedicated service which can run on dedicated hardware
* it does not require a database (for keeping a reference)
* the images can be served by a different subdomain allowing to increase the client side (browser) speed loading a page
* having a dedicated service offloads the image serving from eZ Publish

2) It is possible to reach some file system limitations (32000 subfolders limitation) which then breaks the image upload functionality in eZ Publish. The DAM Server avoids those file system limitations by filing images into date folders (year/month).

3) The Mugo DAM allows multiple eZ Publish instances to easily cross-access the image repository. That's very handy in case you archive content into another eZ Publish instance or if you want to share the image repository between a stage instance and a development instance.

4) With the DAM Server you can manage multiple image repositories and define fall-back lookups to other repositories.

5) eZ Publish does not separate between uploaded images and the dynamically created image aliases. Making the system harder to maintain. The DAM Server stores image aliases in dedicated FS directories.

## Glossar
*alias* A keyword that implies rules how to convert an uploaded image to a specific size, ratio and other image transformations

*image_ratio_identifier* Each instance of the eZAttribute can store multiple images the user uploads. Each uploaded image is stored in context
of an image_ratio_identifier

## TODO
* review class options like max file size etc
