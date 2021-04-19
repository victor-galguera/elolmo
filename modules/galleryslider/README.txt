INTRODUCTION:-
Gallery Slider is a simple slider is used for multiple images. It's very simple
to use. When we upload multiple images and we want to display them in a slider,
So this gallery slider will help you.

There are thumbnails, bullets, and navigation. Due to which it makes different
from another slider.

REQUIREMENTS:-
Both comes in core of drupal 8.
-Field
-Image

FEATURES:
-We can use this slider in Image Field and Views.
-This module provide image style configuration named "GallerySlider(600x300)".
-Slider includes 3 style.
	1. Grid style with thumbnails pics. (Check attached GIF)
	2. Grid style without thumbnails pics. (Check attached GIF)
	3. Nav buttons instead of pics. (Check attached GIF)

OPTIONS:-
-Menu Options. (Grid, Nav)
-Show Images.
-Transition Speed.
-Image style, better to select the same style which is attached with the
module name is "GallerySlider(600x300)"

INSTALLATION:-
-Installation For Field
  1.First of all install the Gallery Slider module.
  2.Then add multiple images to Article content type.
  3.Flush your cache.
  4.Change content type(article) image field from "limited" to "Unlimited".
  5.After that go to display settings.
  6.Change "image" formatter to "Field Gallery Slider".
  7.Important thing is to change Image style to GallerySlider(600x300).
  8.Change settings and press update.
-Installation For View:
  1.Add multiple images to Content type(article or your own).
  2.Create view and change format to gallerySlider.
  3.Then add Image field in FIELDS option.
  4.IMPORTANT:On FIELDS, click on "Content:Image" field change image style to 
  GallerySlider(600x300) and then click on "Multiple Field Settings" change
	display type from ", separate" to "Unordered list "
  5.Save the view and enjoy the Fun.

CONFIGURATION:-
 -There is a configuration of Image style named "GallerySlider(600x300)" in
 config->install->image.style.galleryslider.yml file.
 -Permission depends on views, if you give permission to view,same permission
 implemented on this slider.

That's it if you have any queries let me know 
riazsaid15@yahoo.com

MAINTAINERS:-
DRUPAK:-  Given Motivation, Environment, Traning and Solved Issues

That's it if you have any queries let me know 
riazsaid15@yahoo.com

Thanks,
Riaz
