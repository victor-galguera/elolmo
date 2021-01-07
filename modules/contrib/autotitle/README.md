Autotitle module for Drupal
---------------------------

INTRODUCTION
-----------
  This module allows to automatically set node title from
   the heading tags (<h1>-<h4>).

INSTALLATION:
-------------
  1. Extract the tar.gz into your 'modules' directory or get it
     via composer: composer require drupal/autotitle.
  2. Go to "Extend" after successfully login into admin.
  3. Enable the module at 'administer >> modules'.

REQUIREMENTS
------------
  Autotitle is standalone module, and it has no dependencies
  or any additional requirements.

CONFIGURATION
-------------
  1. Go to /admin/structure/types/manage/[node_type].
  2. Under the tab "Autotitle" there is option to enable automatic title.
     check it to enable autotitle functionality. Remember that after
     enabling it, the title field will be hidden on your node form, 
     but you can revert it at any time by visiting 
     /admin/structure/types/manage/[node_type]/form-display.
  3. You need to choose source field in order to autotitle works, 
     by default it is the body field. Available fields are only the type
     of string, string_* and text, text_*
  4. You can set the fallback title for cases in which there was no heading
     in source field found, we need to setup fallback title. 
     By default it is "Untitled [content_type]"

UNINSTALLATION
--------------
  1. Go to /admin/modules/uninstall and find autotitle module.
  2. Uninstall the module

MAINTAINERS
-----------
  Current maintainers:
   * Mariusz Andrzejewski (sayco) - https://www.drupal.org/u/sayco
