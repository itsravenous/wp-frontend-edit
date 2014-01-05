#Front-End Edit for Wordpress#
This plugin implements a class that will update the current post/pages content based on a form submission. It does not currently provide the form - the fields required can be inferred by reading the source code (though this will be documented soon).

This plugin will never inject styles or javascript into the page; instead you are encouraged to have your theme add any required CSS and JS to enhance the form (perhaps to add a WYSIWYG)

User input is sanitised with [HTML Purifier](http://htmlpurifier.org/) which you'll need to install separately (see the README in htmlpurifier/ for instructions).
