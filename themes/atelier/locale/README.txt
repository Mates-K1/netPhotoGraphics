Atelier translations
====================
netPhotoGraphics has no separate theme translations: theme strings are plain gettext() calls translated
through the core language file (npgCore/locale/<lang>/LC_MESSAGES/core.po/.mo) or, surviving updates,
through plugins/locale/<lang>/LC_MESSAGES/ (used as soon as that folder exists).

atelier.pot       - template with all strings specific to Atelier
atelier-cs_CZ.po  - Czech translation of those strings
(a complete Czech core.po/.mo with the Atelier strings added comes in the separate package)
