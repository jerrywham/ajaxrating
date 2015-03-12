<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Aide</h2>
<p>Fichier d&#039;aide du plugin ajaxrating</p>

<p>
Le paramètrage est basique et ne nécessite que l'appel de hook dans les pages dans lesquelles on peut appeler un ou des articles : home, article, archives, categorie ou tags.&nbsp;</p>

<p>Les paramètres disponibles dans le hook sont :</p>
<ul>
    <li>un index qui doit être unique sur une page. Cela ne veut pas dire que l'on ne peut appeler qu'une seule fois la barre de vote, mais que chaque barre de vote doit avoir un index différent. Cet index est obligatoire.</li>

    <li>le numéro de l'article (obligatoire également) : $plxMotor->plxRecord_arts->f('numero')</li>

    <li>le nombre d'étoiles à afficher (optionnel). Par défaut, il est fixé à 10.</li>

    <li>le paramètre "static" qui cloture les votes (optionnel)</li>
<ul>
<p>Le hook final ressemble donc à ça :</p>
<pre>
&lt;?php eval($plxShow->callHook('rating_bar', array('8', $plxMotor->plxRecord_arts->f('numero') ))); ?&gt; - 10 étoiles (default), ID égal à 8
&lt;?php eval($plxShow->callHook('rating_bar', array('8xxa', $plxMotor->plxRecord_arts->f('numero'),'5' ))); ?&gt; - 5 étoiles, ID égal à 8xxa
&lt;?php eval($plxShow->callHook('rating_bar', array('9a', $plxMotor->plxRecord_arts->f('numero'),'5','static' ))); ?&gt; - 5 étoiles, ID égal à 9a, static (arrêt des votes)</pre>

