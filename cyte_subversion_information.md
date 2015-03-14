# CyTE Repository Help #

## General Subversion Information ##
The CyTE development team currently uses [Subversion](http://subversion.tigris.org/) for version control. In order to download the latest development version of CyTE, you will need to have the [Subversion client](http://subversion.tigris.org/) installed locally or on your development machine (alternatively, you can download the latest distribution release on our [downloads page](http://code.google.com/p/cyte/downloads/list)).

For more information, including installation instructions, visit http://subversion.tigris.org/.

## Checking out the latest version of CyTE ##

  * Repository location: svn checkout http://cyte.googlecode.com/svn/trunk/

To Checkout CyTE from the terminal, enter the following (replace '**{local directory}**' with the name of the folder you want Subversion to create the CyTE files in):

```
svn checkout http://cyte.googlecode.com/svn/trunk/ {local directory}
```

## Contributing to the CyTE Repository ##
If you are a member of the CyTE Development team, you can contribute code to the repository using the following command (replace '**{user.name}**' with your Google user ID):
```
svn checkout https://cyte.googlecode.com/svn/trunk/ cyte --username {user.name}
```

## Using CyTE as a Library ##
Browse to the directory that will contain the library folder.
```
svn propedit svn:externals .
```
It will open an edit window, enter the following
```
engine http://cyte.googlecode.com/svn/tags/latest_stable/cyte/engine/
```
If you want to use a different folder than engine, you can change the $template\_conf['engine\_path'] in the config file.