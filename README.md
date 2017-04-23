# [MonBlog](http://github.com/bpesquet/MonBlog)

Support de l'article [Evoluer vers une architecture MVC en PHP] (http://bpesquet.developpez.com/tutoriels/php/evoluer-architecture-mvc/)

Auteur : [Baptiste Pesquet](https://github.com/bpesquet)


## Description

* Cette version finale utilise un framework MVC.
* Les autres versions sont disponibles sur les différentes branches du dépôt.
* [Démonstration en ligne] (http://monblog.bpesquet.fr/) (l'ajout de commentaires est désactivé)



Commandes (déploiement, etc.)
=============================

Installation
------------

Les commandes nécéssitent principalement fabric, et ont quelques dépendances.
Sur une debian-like:

    $ aptitude install fabric python-path yui-compressor

Utilisation
-----------

L'utilisation se fait via la commande `fab [macommande] [arg]`, en se plaçant dans le repertoire où est cloné le dépot git.
Les commandes usuelles sont (liste non exhaustive) :

    # Déploiement
    $ fab deploy  # Pour déployer sur nt110
    $ fab deploy:prod  # Pour déployer en production

    # Pour utiliser le serveur php afin de debugger en local sans avoir de conf apache
    $ fab serve  # Pour cairn
    $ fab serve:int  # Pour cairn international

    # Pour nettoyer le cache redis en prod
    $ fab clean_redis

Les différentes commandes sont listées en utilisant

    $ fab -l

Pour obtenir plus d'informations sur une commande particulière :

    $ fab -d [macommande]


En savoir plus
--------------

Documentation de fabric : http://docs.fabfile.org/en/1.10/index.html
Documentation de path.py : http://pythonhosted.org/path.py/api.html

fabric est un utilitaire permettant de faciliter l'écriture de script d'administration
path.py est une bibliothèque facilitant la manipulation des fichiers
yui-compressor est un utilitaire pour compresser les fichiers css et js
