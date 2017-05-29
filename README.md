CONTENTS OF THIS FILE
=====================
   
 * Introduction
 * Features
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
==============
This module provides a provision for adding custom messages for node actions.


FEATURES:
========

The Colorbox module:

* Works as a Formatter for nodes.
* Choose between a default messages and custom message that are created.


REQUIREMENTS
============

Requires "Token" module and "Custom Message module".


INSTALLATION
===========
Module has dependency on Token module.You can download the module from
https://www.drupal.org/project/token

* Download and unpack the Token Module and Custom Message Module.

* Go to "Administer" -> "Extend" and enable the Custom Message module

Finally, visit the admin settings form admin/config/user-interface/custom_message
to customize drupal default message. 


CONFIGURATION
=============

1. Select the "content type" from corresponding dropdown.
2. Select the "Action Required" from corresponding dropdown.
3. Create the "Message" in the corresponding textfield, you may use tokens as well.
4. Click "Add More" to add another row.
5. Click "Remove" to delete mistakenly added row or any unwanted row.
6. Click "Save" to complete the configuration.


Drush:
======
A Drush command to install module.

% drush en custom_message

The command will enable the module.

MAINTAINERS
===========

Current maintainers:

 * Kirti Garg - https://www.drupal.org/user/3519018
