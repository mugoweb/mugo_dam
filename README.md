mugo_dam
========

mugo_dam stands for Mugo Digital Asset Manager. It has 2 parts: the Mugo DAM Server and this
eZ Publish extension (client side integration to access the DAM Server). This eZ Publish extension comes
with a new datatype for images. The datatype features an enhanced image upload interface supporting drag and drop of images.

Images uploaded by the user do not get stored in the usual way eZ Publish stores content images; instead, images are stored on the Digital Asset Server. The Digital Asset Server is a separate service (idependent of eZ Publish)
supporting image-uploading and image-serving in different formats (similar to eZ Publish's image aliases).

The way eZ Publish serves images has some downsides which the DAM Server addresses and solves:

Performance
----------------
1) The way eZ Publish serves images in a cluster setup is slow. It servers the images from a shared file
system (like NFS) and keeps a reference for each image (and image alias) in a cluster database. You
easily end up with millions of entries in that cluster database for bigger sites.
The Digital Asset Server improves the performance here: 
 - it servers the images directly from the local disk
 - it is a dedicated service which can run on dedicated hardware
 - it does not require a database (for keeping a reference)
 - the images can be served by a different subdomain allowing to increase the client side (browser)
   speed loading a page
 - having a dedicated service offloads the image serving from eZ Publish

2) It is possible to reach some file system limitations (32000 subfolders limitation) which
then breaks the image upload functionality in eZ Publish.
The DAM Server avoids those file system limitations by filing images into date folders (year/month).

3) The Mugo DAM allows multiple eZ Publish instances to easily cross-access the image repository. That's
very handy in case you archive content into another eZ Publish instance or if you want to share the image
repository between a stage instance and a development instance.

4) With the DAM Server you can manage multiple image repositories and define fall-back lookups to other
repositories.

5) eZ Publish does not separate between uploaded images and the dynamically created image aliases. Making
the system harder to maintain. The DAM Server stores image aliases in dedicated FS directories.
