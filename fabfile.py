#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Scripts pour faciliter les manipulations concernant le front-office.
"""
from __future__ import unicode_literals, print_function, division
import tempfile
from itertools import chain
from datetime import datetime
from uuid import uuid4
from functools import partial
import re

from fabric.api import run, cd, lcd, env, local, roles, execute, put
from fabric.decorators import task, runs_once
from fabric import colors

from path import Path


__directory__ = Path(__file__).parent


#: (Path) Définition du dépot git
# GIT_REMOTE = 'git@redmine.cairn.info:frontoffice-v3.cairn.info'
GIT_REMOTE = __directory__
#: Le répertoire temporaire
TMP_DIRECTORY = Path(tempfile.gettempdir())
#: (Path) Le template utilisé pour construire le build
TEMPLATE_CAIRN_RELEASE = "cairn-release-{version}-cairn"

REGEXPS = {
    'build_gabarit': re.compile(r'(<!--\s+cairn-build\s+::\s+\[)\w+(\]\s+-->)'),
    'stylesheet': re.compile(r'(\s*)<link\s+type=([\'"])text/css\2\s+rel=([\'"])stylesheet\3\s+href=([\'"])(.*?)\4\s*>'),
    'javascript': re.compile(r'(\s*)<script\s+type=([\'"])text/javascript\2\s+src=([\'"])(.*?)\3\s*>\s*</script>'),
    'font-face-paths': re.compile(r'src:\s+url\(([\'"])(?P<src>.*?)\1\)'),
}


#: (dict) Définitions des environnements de tests et de production
env.roledefs = {
    'test': ['root@nt110.reseaucairn.info'],
    'prod': ['cairn@cairn.info:2203', 'cairn@cairn.info:2204'],
}
# Alias pour les l'hôte de test
env.roledefs['reseaucairn'] = env.roledefs['test']
env.roledefs['nt110'] = env.roledefs['test']
# Alias pour l'hôte de prod
env.roledefs['cairn'] = env.roledefs['prod']
env.roledefs['caiweb'] = env.roledefs['prod']



def factory_sub_file(concat_css, git_dir, substitution, to_remove_files, group_to_match):
    """
    Récupère toutes les urls des stylesheets, concatène les fichiers pointés en un seul
    et retourne l'adresse du nouveau fichier à la première balise link trouvée

    PARAMETERS
    ==========
    concat_css: list
        Chaque élément de cette liste contient le contenu d'un des fichiers css à concatener
    git_dir: Path
        Le
    concat_css_file: Path
        Le chemin du nouveau fichier après concatenation
    """
    def sub_file(matchobj):
        path = git_dir / matchobj.group(group_to_match)
        result = ''
        if not concat_css:
            result = matchobj.group(1) + substitution
        concat_css.append(path.text(encoding='utf-8'))
        to_remove_files.append(path)
        return result
    return sub_file

factory_sub_css = partial(factory_sub_file, group_to_match=5)
factory_sub_js = partial(factory_sub_file, group_to_match=4)



# def factory_sub_js(concat_js, git_dir, concat_js_file, to_remove_files):
#     """
#     Récupère toutes les urls des stylesheets, concatène les fichiers pointés en un seul
#     et retourne l'adresse du nouveau fichier à la première balise link trouvée

#     PARAMETERS
#     ==========
#     concat_js: list
#         Chaque élément de cette liste contient le contenu d'un des fichiers js à concatener
#     git_dir: Path
#     concat_js_file: Path
#         Le chemin du nouveau fichier après concatenation
#     """
#     def sub_js(matchobj):
#         path = git_dir / matchobj.group(4)
#         result = ''
#         if not concat_js:
#             result = matchobj.group(1) + '<script type="text/javascript" src="./%s"></script>' % concat_js_file.relpath(git_dir)
#         concat_js.append(path.text(encoding='utf-8'))
#         to_remove_files.append(path)
#         return result
#     return sub_js



def hashing_fonts_path(css_dir):
    """
    Rajoute le hash md5 du fichier font au nom du fichier font lui-même.

    PARAMETERS
    ==========
    css_dir: Path
        Le repertoire qui contient les css
        Cela permettra de recalculer le chemin relatif des fonts
    """
    def sub_font_src(matchobj):
        font_file = (css_dir / Path(matchobj.group('src'))).abspath()
        hexhash = font_file.read_hexhash('md5')
        new_font_path = font_file.parent / (font_file.namebase + '-' + hexhash + font_file.ext)
        font_file.move(new_font_path)
        return 'src: url("%s")' % (new_font_path.relpath(css_dir))
    return sub_font_src



@runs_once
def new_build(version=None, template_cairn_release=TEMPLATE_CAIRN_RELEASE):
    """
    Création de la build (numérotation de la version)

    PARAMETERS
    ==========
    version: int|str (optionnal)
        La version du site. Par défaut, récupère la version la plus récente sur le dépot git et incrémente de 1
    template_cairn_release: str (optionnal)

    template_cairn_release: str (optionnal)
        Le template qui sera utilisé pour le nom de l'archive et le tag git
        Si non défini, prendra la valeur défini dans TEMPLATE_CAIRN_RELEASE

    RETURN
    ======
    str: la build
    """
    # Initialisation du build
    if version is None:
        version = list_version()[-1] + 1
    build = template_cairn_release.format(version=version)
    print(colors.green('Build : %s' % build))
    return build



@roles('test')
def deploy_test(path_cairn='/var/www/cairn', path_cairnint='/var/www/cairn-int'):
    """
    Déploiement du front-office sur les serveurs de tests.
    Se fait par simple clonage du dépot git.
    Le dépot sera réinitialisé sur les serveurs. Si des modifications ont été effectués sur
    le serveur de test, elles seront effacées.

    PARAMETERS
    ==========
    path_cairn: str (optionnal)
        Le chemin sur le serveur où sera déployé le front-office pour cairn
        Par défaut à /var/www/cairn
    path_cairnint: str (optionnal)
        Le chemin sur le serveur où sera déployé le front-office pour cairn-int
        Par défaut à /var/www/cairn-int
    """
    with cd(path_cairn):
        print(colors.blue("Déploiement du code pour cairn sur nt110"))
        run("git reset --hard")
        run("git checkout master")
        run("git pull origin master")
        print(colors.green("Déploiement du code pour cairn sur nt110 réussi"))
    with cd(path_cairnint):
        print(colors.blue("Déploiement du code pour cairn-int sur nt110"))
        run("git reset --hard")
        run("git checkout master")
        run("git pull origin master")
        print(colors.green("Déploiement du code pour cairn-int sur nt110 réussi"))



@task
@runs_once
def pre_deploy_prod(build, git_remote=GIT_REMOTE, minify_static=True):
    """
    Prepare en local l'archive du front-office qui sera envoyé sur les serveurs de production

    PARAMETERS
    ==========
    version: int|str (optionnal)
        La version du site. Sera utilisé pour la création de l'archive et le tag git
        Si non défini, la dernière version sera récupéré en utilisant les tags git
        et en incrémentant de 1 ce numéro

    git_remote: str (optionnal)
        Le lien vers le dépot git pour le front-office.
        Si non défini, prendra la valeur défini dans GIT_REMOTE

    minify_static: bool (optionnal)
        Si les fichiers statique (css et js par exemple) doivent être concaténer et minifier
        Utilise yui-compressor
        Par défaut à True

    template_cairn_release: str (optionnal)
        Le template qui sera utilisé pour le nom de l'archive et le tag git
        Si non défini, prendra la valeur défini dans TEMPLATE_CAIRN_RELEASE
    """
    print(colors.blue("Préparation de l'archive pour la release %s" % build))
    now = datetime.now().strftime('%Y%m%dT%H%M%S')
    # Création du repertoire de travail. Sera effacé si existe déjà
    release_dir = TMP_DIRECTORY / build
    if release_dir.exists():
        release_dir.rmtree()
    release_dir.makedirs_p()
    print(colors.blue("Création du répertoire %s" % release_dir))
    # Clone du dépot git
    with lcd(release_dir):
        local('git clone %s cairn' % git_remote)
        git_dir = release_dir / 'cairn'

    # Suppression des fichiers qui ne doivent pas aller sur les serveurs de prod
    with lcd(git_dir):
        print(colors.blue("Suppression des fichiers ne devant pas être en prod"))
        (git_dir / '.git').rmtree()
        (git_dir / '.gitignore').remove()
        (git_dir / '.gitlab-ci.yml').remove()
        (git_dir / 'fabfile.py').remove()
        (git_dir / '.mailmap').remove()
        (git_dir / 'info.php').remove()
        (git_dir / 'README.md').remove()
        for config_files in (git_dir / 'Config').files('*.ini'):
            config_files.remove()
        # On ajoute le numéro de build dans le gabarit
        # afin de constater si la release a bien été effectué en production
        print(colors.blue("Ajout de la version de build dans les fichiers de gabarit"))
        for view_dir in ('Vue', 'VueInt'):
            gabarit_file = git_dir / view_dir / 'gabarit.php'
            gabarit = gabarit_file.text(encoding='utf-8')
            gabarit = REGEXPS['build_gabarit'].sub(r'\1%s\2' % build, gabarit)
            gabarit_file.write_text(gabarit, encoding='utf-8')

    if minify_static:
        static_dir = git_dir / 'static'
        css_dir = static_dir / 'css'
        js_dir = static_dir / 'js'

        # Calcul du hash des fonts pour la mise en cache
        css_fonts_file = static_dir / 'css' / 'font-face.css'
        css_fonts = css_fonts_file.text(encoding='utf-8')
        css_fonts = REGEXPS['font-face-paths'].sub(hashing_fonts_path(css_dir), css_fonts)
        css_fonts_file.write_text(css_fonts, encoding='utf-8')

        to_remove_files = list()

        # Concaténation des fichiers css
        substitution_string = "[[substitution::%s]]" % uuid4()
        for site, view_dir in [('cairn', 'Vue'), ('cairnint', 'VueInt')]:
            header_css_file = git_dir / view_dir / 'CommonBlocs' / 'headerCss.php'
            if not header_css_file.exists():
                continue
            print(colors.blue('Concaténation des fichiers css de %s' % header_css_file))
            concat_css = list()
            header_css = header_css_file.text(encoding='utf-8')
            header_css = REGEXPS['stylesheet'].sub(
                factory_sub_css(concat_css, git_dir, substitution_string, to_remove_files),
                header_css
            )
            if concat_css:
                concat_css_tmp_file = css_dir / ('style-%s.css' % site)
                concat_css_tmp_file.write_text(''.join(concat_css), encoding='utf-8')
                hexhash = concat_css_tmp_file.read_hexhash('md5')
                concat_css_file = concat_css_tmp_file.parent / (concat_css_tmp_file.namebase + '-' + hexhash + concat_css_tmp_file.ext)
                concat_css_tmp_file.move(concat_css_file)
                header_css = header_css.replace(
                    substitution_string,
                    '<link type="text/css" rel="stylesheet" href="./%s">' % concat_css_file.relpath(git_dir)
                )
                header_css_file.write_text(header_css, encoding='utf-8')
                # On crée un lien symbolique pour que le css soit facilement retrouvable par des programmes en ayant besoin
                with lcd(concat_css_file.parent):
                    local('ln -s %s style-%s.min.css' % (concat_css_file.name, site))


        # Concaténation des fichiers js
        substitution_string = "[[substitution::%s]]" % uuid4()
        for site, view_dir in [('cairn', 'Vue'), ('cairnint', 'VueInt')]:
            header_js_file = git_dir / view_dir / 'CommonBlocs' / 'footerJavascript.php'
            if not header_js_file.exists():
                continue
            print(colors.blue('Concaténation des fichiers js de %s' % header_js_file))
            concat_js = list()
            header_js = header_js_file.text(encoding='utf-8')
            header_js = REGEXPS['javascript'].sub(
                factory_sub_js(concat_js, git_dir, substitution_string, to_remove_files),
                header_js
            )
            if concat_js:
                concat_js_tmp_file = js_dir / ('javascript-%s.js' % site)
                concat_js_tmp_file.write_text(''.join(concat_js), encoding='utf-8')
                hexhash = concat_js_tmp_file.read_hexhash('md5')
                concat_js_file = concat_js_tmp_file.parent / (concat_js_tmp_file.namebase + '-' + hexhash + concat_js_tmp_file.ext)
                concat_js_tmp_file.move(concat_js_file)
                header_js = header_js.replace(
                    substitution_string,
                    '<script type="text/javascript" src="./%s"></script>' % concat_js_file.relpath(git_dir)
                )
                header_js_file.write_text(header_js, encoding='utf-8')
                # On crée un lien symbolique pour que le js soit facilement retrouvable par des programmes en ayant besoin
                with lcd(concat_js_file.parent):
                    local('ln -s %s javascript-%s.min.js' % (concat_js_file.name, site))


        for file_ in to_remove_files:
            if file_.exists():
                file_.remove()
        # Minification des fichiers statics (js/css)
        print(colors.blue('Minification des fichiers statiques'))
        iter_static_dir = chain(
            static_dir.walkfiles('*.css'),
            static_dir.walkfiles('*.js')
        )
        for static_path in iter_static_dir:
            # On saute les fichiers déjà minifiés
            if '.min.' in static_path:
                continue
            staticmin_path = static_path + '.min'
            local('minify -o {staticmin_path} {static_path}'.format(**locals()))
            static_path.remove()
            staticmin_path.rename(static_path)

    # Création de l'archive
    archive_path = TMP_DIRECTORY / build + '.tar.gz'
    print(colors.blue("Création de l'archive à envoyée en production"))
    with lcd(TMP_DIRECTORY):
        local('tar -zcf {archive_path!s} {build!s}'.format(**locals()))
    release_dir.rmtree()
    print(colors.green("L'archive est prête à être envoyé en production"))
    return archive_path


@roles('prod')
def deploy_prod(build, archive_path, site_path='/data/www/sites/'):
    """
    Déploiement du front-office sur les serveurs de production

    PARAMETERS
    ==========
    build: str
    archive_path: str|Path
        Le chemin de l'archive contenant la release du site
    site_path: str|Path (optionnal)
        Le chemin où sera déployé la release. Par défaut à /data/www/sites
    """
    print(colors.blue("Déploiement de l'archive %s en production" % archive_path))
    site_path = Path(site_path)
    archive_path = Path(archive_path)
    if not archive_path.exists():
        raise OSError("L'archive %s n'existe pas" % archive_path)
    put(archive_path, site_path)
    with cd(site_path):
        run('tar -zxf {site_path}{archive_path.name} -C {site_path}'.format(**locals()))
    release_path = site_path / build
    with cd(release_path):
        print(colors.blue("Lancement du script de déploiement"))
        install_script_path = release_path / 'cairn' / 'Deploy' / 'install.sh'
        run('sh {install_script_path} {release_path.name}'.format(**locals()))
    print(colors.green("La release a bien été effectuée"))



@runs_once
def post_deploy_prod(build):
    """
    Les tâches de post-traitement après déploiement de la production
    Principalement pour le tag sur le dépot git

    PARAMETERS
    ==========
    build: str
        La version de la release, utilisé pour le tag git
    """
    with lcd(__directory__):
        print(colors.blue("Création du tag git"))
        local('git tag %s' % build)
        local('git push origin %s' % build)



@task
@runs_once
def deploy(host='test', build=None):
    """
    Déploie sur le serveur de test par défaut, ou sur le serveur de prod avec `deploy:prod`

    Le déploiement sur le serveur de test consiste uniquement à mettre à jour le dépot git.
    Le déploiement sur la production consiste en la création d'une archive qui est envoyée sur
    le serveur de production et aux déploiements de différents scripts.

    PARAMETERS
    ==========
    host: str (optionnal)
        Le type de serveur où déployer. Par défaut sur les serveurs de tests. Peut prendre les valeurs `test` ou `prod`
    build: str (optionnal)
        La build qui sera utilisé. Par défaut, récupère les builds depuis les tags git et incrémente de 1 la plus récente
        À utiliser uniquement pour le déploiement en production
    """
    role = env.roledefs.get(host)
    if role is env.roledefs['test']:
        execute(deploy_test)
    elif role is env.roledefs['prod']:
        if build is None:
            build = execute(new_build).values()[0]
        archive_path = execute(pre_deploy_prod, build=build).values()[0]
        execute(deploy_prod, build=build, archive_path=archive_path)
        execute(clean_redis)
        execute(post_deploy_prod, build=build)



@task
@runs_once
def rollback():
    """
    (Non implémenté) Retourne à une build antérieur sur le serveur de production
    """
    raise NotImplementedError()


@task
@runs_once
def list_version():
    """
    Liste les différentes versions de release disponible sur le dépot git
    """
    print(colors.blue('Récupération de la version pour la release'))
    local("git fetch --tags")
    versions = local(
        r"""git ls-remote --tags {remote} | grep -oP "(?<=cairn-release-)\d+(?=-cairn)?" """.format(remote=GIT_REMOTE),
        capture=True
    )
    versions = versions.split('\n')
    versions = [v.strip() for v in versions]
    versions = filter(None, versions)
    versions = {int(v) for v in versions}
    versions = sorted(versions)
    print(colors.green("Les versions de cairn : {versions}".format(versions=versions)))
    return versions



@task
@roles('prod')
def clean_redis():
    """
    Vide le cache redis pour cairn et cairn-int
    """
    print(colors.blue("Nettoyage du cache redis"))
    run("redis-cli -n 2 flushdb")
    run("redis-cli -n 5 flushdb")
    print(colors.green("Nettoyage du cache réussi"))



@task
@roles('prod')
@runs_once
def synchronize_couv():
    print(colors.blue("Synchronisation des couvertures entre nt110 et la prod"))
    # run('rsync --progress -r --delete -t -e"ssh -p 23085" root@v3.reseaucairn.info:/var/www/html/vign_rev/ /data/www/sites/cairn_includes/vign_rev/')
    print(colors.green("Synchronisation des couvertures réussi"))



@task
@runs_once
def serve(site="cairn", hostname=None, port=8080, debug=False, php_version="5"):
    """
    Lance le serveur php en vue de debug (serve:int pour cairn international)

    PARAMETERS
    ==========
    site: str (optionnal)
        Peut-être à `cairn` ou `int`.
        Si à cairn ET que le paramètre hostname est None, alors le serveur sera lancé sur localhost
        Si à int ET que le paramètre hostname est None, alors le serveur sera lancé sur 0.0.0.0
    hostname: str (optionnal)
        L'adresse où écoutera le serveur php.
    port: int (optionnal)
        Le port où écoutera le serveur php
        Par défaut à 8080
    debug: str (optionnal)
        Si à `true|on|1`, active xdebug
        Par défaut, n'est pas activé
    """
    if site in ('cairn',) and hostname is None:
        hostname = 'localhost'
    elif site in ('cairnint', 'cairn-int', 'int') and hostname is None:
        hostname = '0.0.0.0'
    print(colors.blue('Lancement du serveur php sur http://%s:%i\nCtrl^C pour quitter' % (hostname, port)))
    params = []
    if debug and debug.lower() in ('true', 'on', '1'):
        params.append('-d display_errors=On')
    else:
        params.append('-d display_errors=Off')
    local("php%s %s -S %s:%i" % (php_version, ' '.join(params), hostname, port))
