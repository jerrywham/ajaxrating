(Unobtusive) AJAX Rating Bars v 1.2.2 (March 18 2007)
ryan masuga, ryan@masugadesign.com (http://www.masugadesign.com)

Homepage for this script:
http://www.masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/
=============================================================================
This (Unobtusive) AJAX Rating Bar script is licensed under the 
Creative Commons Attribution 3.0 License - http://creativecommons.org/licenses/by/3.0/

What that means is: Use these files however you want, but don't redistribute 
without the proper credits, please. I'd appreciate hearing from you if you're
using this script. Credits should include:
- Masuga Design (http://masugadesign.com/the-lab/scripts/unobtrusive-ajax-star-rating-bar/)
- Komodo Media (http://komodomedia.com) 
- Climax Designs (http://slim.climaxdesigns.com/).
- Ben Nolan (http://bennolan.com/behaviour/) for Behavio(u)r!
- Cyril MAGUIRE (http://www.ecyseo.net/) for Pluxml plugin

Suggestions or improvements welcome - they only serve to make the script better.
=============================================================================
-----------------------------------------------------------------------------

CHANGELOG:
-----------------------------------------------------------------------------
v 1.3 (April 29, 2013):

 * Features: Adaptation for Pluxml plugin
-----------------------------------------------------------------------------
v 1.2.2 Updates (March 18, 2007):

 * ADDED: prefixed DB calls with $rating_dbname for better separation from other scripts
          This will really help with Wordpress installs - As of 1.2.2 I don't think
          you need to do any special tweaks.

 * UPDATED: changed some variable names so as not to confuse with other scripts

-----------------------------------------------------------------------------
v 1.2.1 Updates (March 18, 2007):

 * ADDED: a new check to keep voters from faking the vote to something very high
 * ADDED: a check to stop people from voting multiple times
 * ADDED: example of using 'static' to the Read Me

 * UPDATED: Use echo now to render your star rater, because I'm using 'return' 
            instead of echo in the function

 * FIXED: bug where you couldn't have letters in your id. Now, letters and numbers only
 * FIXED: issue with number of votes cast (i.e. '0') not showing up for new IDs

-----------------------------------------------------------------------------
v 1.2 Updates (March 11, 2007):

 * ADDED: a couple checks to stop possible SQL injection hacks
 * ADDED: 'rel="nofollow"' to the rendered rating bar links
 * ADDED: Automatic inserting of ID's in the DB
 * ADDED: New variables to make it easier to fix your paths!
 * ADDED: Ability to call a rater "static" so you can't vote
          (Might come in handy if someone needs to be logged in to vote...)

 * FIXED: SQL in this readme to use backticks for easier copy-n-paste
 * FIXED: modified a couple CSS styles that were giving people headaches

-----------------------------------------------------------------------------
v 1.1 Features:
 * Uses unobtrusive Javascript, so ratings will still work if the user has Javascript off
   (the script has been tested in IE 6, Safari, and FF).
 * keeps Javascript out of the HTML, resulting in cleaner markup
 * There are now some checks in place to discourage monkey-business, like negative numbers, or funky IP's
 * IP lockout is now in the script
 * You can now specify the number of units! If you want 5 stars, just add a 5, otherwise the script defaults to 10.
 * Enter database info in one place rather than three places
 * This script only uses ONE image
-------------------------------------------------------------

HOW TO USE PLUXML PLUGIN

Hook can be used only in file where articles can be found (home, article, archives, categorie or tags).

<?php eval($plxShow->callHook('rating_bar', array('8', $plxMotor->plxRecord_arts->f('numero') ))); ?> - 10 stars (default), ID of 8
<?php eval($plxShow->callHook('rating_bar', array('8xxa', $plxMotor->plxRecord_arts->f('numero'),'5' ))); ?> - 5 stars, ID of 8xxa
<?php eval($plxShow->callHook('rating_bar', array('9a', $plxMotor->plxRecord_arts->f('numero'),'5','static' ))); ?> - 5 stars, ID of 9a, static (non votable)
<?php eval($plxShow->callHook('rating_bar', array('9b', $plxMotor->plxRecord_arts->f('numero'),'' ))); ?>  - 10 stars, ID of 9b
<?php eval($plxShow->callHook('rating_bar', array('9c', $plxMotor->plxRecord_arts->f('numero'),'8','static' ))); ?> - 8 stars, ID of 9c, static (non votable)

If you want to change how the rating bar is rendered, you will need to edit
the ajaxrating.php file (line 266 and following). Also, you might need to edit the bottom of the static.plxrpc.php
file at about line 83, where the $newback variable is.
