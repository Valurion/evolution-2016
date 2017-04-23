// Action sur le menu utilisateur (pdf, version zen, etc...)
$(function() {
    $( document ).ready(function() {
        var $sidebar = $('#usermenu-tools');
        if ($sidebar.length == 0)
            return false;

        // Permet au menu utilisateur de suivre le scroll de l'utilisateur
        var $window = $(window),
            offset = $sidebar.offset(),
            topPadding = 5;
        $window.scroll(function() {
            if ($window.scrollTop() > offset.top) {
                $sidebar.stop().animate({
                    marginTop: $window.scrollTop() - offset.top + topPadding
                });
            } else {
                $sidebar.stop().animate({
                    marginTop: 0
                });
            }
        });
    });
});

$(function() {
    // Fait apparaitre les raccourcis vers certaines partie d'un article
    // suivant si ces parties sont disponible ou non dans le texte

    // Raccourci vers le résumé
    if ($('#from_xml_bottom .abstract:first').length) {
        $('#link-abstract').show();
    }
    // Raccourci vers le plan de l'article
    if ($('#plan-of-article').length) {
        $('#link-plan-of-article').show();
    }
});



/****************************************************************************
 * Calcul sur la hauteur des affiliations
 * --------------------------------------------------------------------------
 * Les affiliations qui dépassent la hauteur de la couverture doivent être
 * repliés au chargement de la page, pour des raisons graphiques.
 * Quand on clique sur une affiliation, alors ça déploie toutes les affiliations
 * repliés.
 ****************************************************************************/
// On masque les affiliations jusqu'au chargement complet de toute les images
$(function() {
    $('#infoBook .auteur').hide(0);
});
// Les images ont été chargés, on réaffiche les affiliations et on commence les traitements
$(window).on('load', function() {
    "use strict";

    var $infoBook = $('#infoBook');
    var $divAuthors = $infoBook.find('.auteur');
    $divAuthors.fadeIn();
    var max_affiliation_height = null;

    // On calcule la hauteur maximale d'une affiliation selon le nombre d'affiliation
    var countAuthors = $divAuthors.length;
    if (countAuthors === 0) {
        return;
    } else if (countAuthors === 1) {
        max_affiliation_height = 153;
    } else if (countAuthors === 2) {
        max_affiliation_height = 80;
    } else if (countAuthors === 3) {
        max_affiliation_height = 42;
    } else {
        max_affiliation_height = 24;
    }


    var $divAffiliations = $divAuthors.find('.affiliation');
    var affiliations = [];

    $divAffiliations.each(function(index, elem) {
        var $elem = $(elem);
        var currentHeight = $elem.height();
        if (currentHeight <= max_affiliation_height) {
            return;
        }
        // Création d'une div qui indique que l'affiliation est refermé
        var $divArrow = $('<div class="arrow down"></div>');
        $elem.after($divArrow);
        $elem.data('arrow', $divArrow);
        // On enregistre les informations d'hauteurs et d'états de l'affiliation
        $elem.data('is-deploy', false);
        $elem.data('max-height', $elem.height());
        $elem.data('min-height', max_affiliation_height);
        $elem.height(max_affiliation_height);
        affiliations.push($elem);
        // On bind le handle au click sur l'affiliation
        $elem.click(handleClickAffiliation);
    });

    // Déploiement des affiliations quand on click sur une d'entre elle
    function handleClickAffiliation(ev) {
        $.each(affiliations, function(index, elem) {
            var $elem = $(elem);
            var height = null;
            if ($elem.data('is-deploy')) {
                height = $elem.data('min-height');
                $elem.data('is-deploy', false);
            } else {
                height = $elem.data('max-height');
                $elem.data('is-deploy', true);
            }
            // L'affiliation se déploie ou non suivant son état,
            // et on modifie également l'indicateur de déploiement
            $elem.animate({height: height}, function() {
                var $arrow = $elem.data('arrow');
                if ($elem.data('is-deploy')) {
                    $arrow.addClass('up');
                    $arrow.removeClass('down');
                } else {
                    $arrow.addClass('down');
                    $arrow.removeClass('up');
                }
            });
        });
    }
});



/****************************************************************************
 * Ajustement des amorces et figures
 * --------------------------------------------------------------------------
 * Les amorces et les figures sont décalés sur la gauche à l'affichage.
 * On ajuste ces décalages verticaux et horizontaux dans la fonction suivante
 ****************************************************************************/
var THREE_DOT = "...";
var ANIMATE_SPEED = 300;
var RENVOI_MARGIN_LEFT = 10;
var RENVOI_MARGIN_TOP = 15;
var LINE_HEIGHT_AMORCE = 30;
//var ON_TRANSITION = !!$('#transform_data[data-version="13032014"]').length;


var ajust_amorce = function() {
    var renvoi, index, amorce, bound_box, type_obj, figure_titre, nextSibling, current;
    var $corps = $('#from_xml_bottom').first();
    if ($corps.length <= 0) return;
    // On commence par définir les figures comme des amorces de notes (sur le coté gauche en petit)
    // Pour pouvoir par la suite calculer leur position en hauteur, on préfère le faire à l'avance
    // plutôt que dans la boucle qui parcourt les mêmes élements plus bas.
    $corps.find('div.figure').addClass('amorce');
    var $objects = $corps.find('.renvoi.note, div.figure');
    var corps_offset_x = $corps[0].getBoundingClientRect().left;
    var length = $objects.length;
    for (index = 0; index < length; index++) {
        obj = $objects[index];
        type_obj = "figure";
        // On vérifie le type d'objet (figure ou renvoi)
        if (obj.className.indexOf('renvoi') > 0) {
            nextSibling = obj.nextSibling;
            while (true) {
                if (nextSibling == null)
                    break
                else if (!nextSibling || nextSibling.nodeType != 1)
                    nextSibling = nextSibling.nextSibling;
                else
                    break;
            }
            // Le renvoi en lui-même ne nous intéresse pas. On souhaite l'amorce qui y est associé.
            // Normalement, elle doit être le premier élement précedant l'objet courant. Si ce n'est pas le cas,
            // on passe à l'élement suivant.
            if (!nextSibling || !nextSibling.className || nextSibling.className.indexOf('amorce') < 0)
                continue;
            obj = nextSibling;
            type_obj = "amorce";
        }
        bound_box = obj.getBoundingClientRect();
        // On ferre à droite, le long du corps, les objets.
        obj.style.left = Math.floor((corps_offset_x - (bound_box.left + bound_box.width)) - RENVOI_MARGIN_LEFT) + 'px';
        if (type_obj == 'figure')
            continue;
        // On calcule la hauteur possible et souhaitable pour les amorces.
        // Si la position d'une amorce est supérieur à la position du renvoi|figure suivant,
        // on le réduit, avec un minimum d'une ligne.
        next_obj = $objects[index + 1];
        if (!next_obj)
            continue
        diff = next_obj.getBoundingClientRect().top - bound_box.top;
        diff = diff > LINE_HEIGHT_AMORCE ? diff : LINE_HEIGHT_AMORCE;
        if (bound_box.height > diff) {
            diff = Math.floor(diff / LINE_HEIGHT_AMORCE) * LINE_HEIGHT_AMORCE;
            obj.style.height = diff + 'px';
        }
    }
}
$(ajust_amorce);
/****************************************************************************
 * Fin de l'ajustement des amorces et figures
 * **************************************************************************/



// ###### CODE POUR LIGHTBOX ######
function ElementLightbox(jquery_Element, parent, index) {
    $.extend(this, $('<div class="element"></div>'));
    var self = this;
    self.$this = $this = $(self);
    self.index = index;
    self.parent = parent;
    self.orig = $(jquery_Element);
    self.orig.css('cursor', 'pointer');
    self.title = $('<div class="title" />').append(self.orig.children('.figure_titre, .tableau_titre').clone());
    self.img = new Image();
    self.img.onload = function() {
        self.$this.data('orig_height', this.height);
    }
    self.img.src = self.orig.find('img').attr('src');
    self.a_wrap_img = $('<a href="' + self.img.src + '" target="_blank"></a>').append(self.img);
    self.image = $('<div class="image" />').append(self.a_wrap_img); //self.orig.find('img').clone());
    //self.legend = $('<div class="legend" />').append(self.orig.children().not('.figure_titre, .tableau_titre,' + (ON_TRANSITION ? '.objetmedia' : 'img')).clone());
    self.legend = $('<div class="legend" />').append(self.orig.children().not('.figure_titre, .tableau_titre, .objetmedia, .figure, img').clone());
    self.append(self.title, self.image, self.legend);
    self.orig.on('click', {
        elem: self
    }, function(elem) {
        self.parent.display(self.index);
    });
}
var maximize_height = function(element, animate) {
    // Calcule la taille optimal de l'image dans la lightbox.
    var img_h;
    var image = element.find('div.image');
    var img = element.find('img');
    var title_h = element.find('div.title').get(0).offsetHeight;
    var legend_h = element.find('div.legend').get(0).offsetHeight;
    orig_height = element.data('orig_height');
    img_h = orig_height;
    var image_maximize_height = element.innerHeight() - (title_h + legend_h + (img.outerHeight(true) - img.innerHeight()));
    if (image_maximize_height < img_h) {
        if (animate != true) {
            image.height(image_maximize_height);
        } else {
            image.animate({
                'height': image_maximize_height
            });
        }
    } else if (img_h != image.height()) {
        if (animate != true) {
            image.height(img_h);
        } else {
            image.animate({
                'height': img_h
            });
        }
    }
    // Positonne l'image au centre de la lightbox
    var img_center_y = image_maximize_height - image.height();
    if (img_center_y > 0) {
        img.css('top', img_center_y / 2);
    } else {
        img.css('top', 0);
    }
}

function Lightbox() {
    var self = this;
    self.current_index = 0;
    $.extend(this, $('<div id="lightbox2" />'));
    this.$this = $this = $(this);
    this.canvas = $('<div class="canvas" />');
    this.closer = $('<div class="closer" />');
    this.arrow_left = $('<div class="arrow-left" />');
    this.arrow_right = $('<div class="arrow-right" />');
    this.append(this.closer, this.canvas, this.arrow_left, this.arrow_right);
    this.elements = new Array();
    $('body').append($this);
    //var l_elems = this.l_elems = $('#article_content').find('div.tableau, div.figure').has(ON_TRANSITION ? '.objetmedia img' : 'img');
    var l_elems = this.l_elems = $('#article_content').find('div.tableau, div.figure').has('img');
    for (var i = 0, elem; elem = l_elems[i]; i++) {
        element = new ElementLightbox(elem, this, i);
        this.elements.push(element);
    }
    this.closer.on('click', function() {
        self.fadeOut(ANIMATE_SPEED);
    });
    this.arrow_left.on('click', function() {
        self.prev_element();
    });
    this.arrow_right.on('click', function() {
        self.next_element();
    });
    $(window).on('resize', function() {
        self.on_resize_window();
    }); // Relativement gourmand en perf
    this.locking_event = false; // Pour empêcher que certains évenements s'empilent et provoquent des bugs graphique
    // Evenement clavier
    $(document).keyup(function(event) {
        if (self.is(':visible')) {
            switch (event.which) {
                case 27:
                    self.fadeOut(ANIMATE_SPEED);
                    break;
                case 37:
                    self.prev_element();
                    break;
                case 39:
                    self.next_element();
                    break;
            }
        }
    });
}
// Redimmensionne automatiquement l'image selon la taille de l'écran.
// Pour éviter tout lack en CPU, on place un verrou sur les évenements, que ce soit les évenements de redimmensionnement ou de clavier.
Lightbox.prototype.on_resize_window = function() {
    if (this.locking_event == true || !this.is(':visible')) {
        return
    }
    var self = this;
    this.locking_event = true;
    window.setTimeout(function() {
        self.locking_event = false;
        maximize_height(self.canvas.find('.element').first(), true);
    }, 500);
}
Lightbox.prototype.prev_element = function() {
    if (this.locking_event == true) {
        return
    }
    this.locking_event = true
    var self = this;
    var next_index = (this.current_index > 0) ? (this.current_index - 1) : (this.elements.length - 1)
    var current_element = this.canvas.find('.element').first();
    // this.canvas.append(this.elements[next_index].clone().css('left', -current_element.outerWidth()));
    this.canvas.append(this.elements[next_index].css('left', -current_element.outerWidth()));
    var next_element = this.canvas.find('.element').last();
    maximize_height(next_element);
    this.canvas.animate({
        'left': +current_element.innerWidth()
    }, ANIMATE_SPEED, function() {
        current_element.detach();
        next_element.css('left', 0);
        self.canvas.css('left', 0);
        self.current_index = next_index;
        self.locking_event = false
    });
}
Lightbox.prototype.next_element = function() {
    if (this.locking_event == true) {
        return
    }
    this.locking_event = true
    var self = this;
    var next_index = (this.current_index < this.elements.length - 1) ? (this.current_index + 1) : 0
    var current_element = this.canvas.find('.element').first();
    // this.canvas.append(this.elements[next_index].clone().css('left', +current_element.outerWidth()));
    this.canvas.append(this.elements[next_index].css('left', +current_element.outerWidth()));
    var next_element = this.canvas.find('.element').last();
    maximize_height(next_element);
    this.canvas.animate({
        'left': -current_element.innerWidth()
    }, ANIMATE_SPEED, function() {
        current_element.detach();
        next_element.css('left', 0);
        self.canvas.css('left', 0);
        self.current_index = next_index;
        self.locking_event = false
    });
}
Lightbox.prototype.display = function(elem_index) {
    this.current_index = elem_index;
    this.canvas.empty();
    // this.canvas.append(this.elements[elem_index].clone());
    // this.canvas.append(this.elements[elem_index].clone());
    this.canvas.append(this.elements[elem_index]);
    this.fadeIn(ANIMATE_SPEED);
    maximize_height(this.find('.element'));
    // var title = this.find('.canvas div.title'), image = this.find('.canvas div.image'), legend = this.find('.canvas div.legend'), element = this.find('div.element');
    // this.fadeIn(ANIMATE_SPEED);
    // image.height(element.innerHeight() - (title.outerHeight() + legend.outerHeight()));
}
$(function() {
    new Lightbox();
});


// Recherche de manière récursive la première lettre d'un noeud html
var insert_lettrine = function(elem) {
    if (!elem)
        return false;
    if ((elem.nodeType == 3) && ($.trim(elem.textContent) != '')) {
        var span = document.createElement("span");
        span.textContent = elem.textContent[0];
        elem.parentNode.insertBefore(span, elem);
        elem.textContent = elem.textContent.substr(1);
        span.className = "lettrine";
        window.setTimeout(function() {
            span.style.position = 'relative';
        }, 0.1); // FIX pour firefox. Le float:left défini en css ne fonctionne pas correctement si l'on ne crée pas un contexte de formattage à posteriori.
        //         span.style.styleFloat = "left";  span.style.cssFloat = "left"; // Pour IE
        return true;
    }

    var length = elem.childNodes.length;
    var child;
    for (var i = 0; i < length; i++) {
        child = elem.childNodes[i];
        if (insert_lettrine(child))
            return true;
    }
    return false;
};

var search_last_alinea = function(elem) {
    if (!elem)
        return false;
    var $elem = $(elem);
    $elem.find(".alinea").last().addClass('last');
};


// Les cellules des CALS peuvent être porteuses d'un attribut permettant d'assigner une largeur en post-traitement
var setColumnWidth = function($corps) {
    if ($corps.length <= 0) return;
    $corps.find('td[data-width]').each(function(index, elem) {
        var $elem = $(elem);
        $elem.css('width', $elem.data('width'));
    });
};


$(function() {
    "use strict";

    var $corps = $('#textehtml .corps').first();
    setColumnWidth($corps);
    // On insère la lettrine dans le corps du texte
    var firstAlinea = document.evaluate(
        '//section[contains(@class, "section")]/div[contains(@class, "para")]/p[contains(@class, "alinea")]',
        document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null
    ).singleNodeValue;
//    var firstAlinea = document.evaluate(
//        '//section[contains(@class, "section")]/div[contains(@class, "para")]/p[contains(@class, "alinea")]',
//        $corps[0], null, XPathResult.FIRST_ORDERED_NODE_TYPE, null
//    ).singleNodeValue;
    if (firstAlinea) {
        insert_lettrine(firstAlinea);
    }

    // Ajoute une classe last au dernier paragraphe d'une citation. Ceci afin de pouvoir afficher correctement
    // les guillemets décoratives.
    var $blockquotes = $('#textehtml blockquote');
    var blockquotes_length = $blockquotes.length;
    for (var i = 0; i < blockquotes_length; i++) {
        search_last_alinea($blockquotes[i]);
    }
});
