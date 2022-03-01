/*
 * jQuery Hotkeys Plugin
 * Copyright 2010, John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Based upon the plugin by Tzury Bar Yochay:
 * http://github.com/tzuryby/hotkeys
 *
 * Original idea by:
 * Binny V A, http://www.openjs.com/scripts/events/keyboard_shortcuts/
 */

(function(jQuery) {

    jQuery.hotkeys = {
        version: "0.8",

        specialKeys: {
            8: "backspace", 9: "tab", 13: "return", 16: "shift", 17: "ctrl", 18: "alt", 19: "pause",
            20: "capslock", 27: "esc", 32: "space", 33: "pageup", 34: "pagedown", 35: "end", 36: "home",
            37: "left", 38: "up", 39: "right", 40: "down", 45: "insert", 46: "del",
            96: "0", 97: "1", 98: "2", 99: "3", 100: "4", 101: "5", 102: "6", 103: "7",
            104: "8", 105: "9", 106: "*", 107: "+", 109: "-", 110: ".", 111 : "/",
            112: "f1", 113: "f2", 114: "f3", 115: "f4", 116: "f5", 117: "f6", 118: "f7", 119: "f8",
            120: "f9", 121: "f10", 122: "f11", 123: "f12", 144: "numlock", 145: "scroll", 191: "/", 224: "meta"
        },

        shiftNums: {
            "`": "~", "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&",
            "8": "*", "9": "(", "0": ")", "-": "_", "=": "+", ";": ": ", "'": "\"", ",": "<",
            ".": ">",  "/": "?",  "\\": "|"
        }
    };

    var sequenceTimeout, sequenceKeys = [];

    function keyHandler(handleObj) {
        if (typeof handleObj.data === "string") {
            handleObj.data = {
                combi: handleObj.data,
                disableInInput: true
            };
        }

        // Only care when a possible input has been specified
        if (typeof handleObj.data !== "object" ||
                handleObj.data === null ||
                typeof handleObj.data.combi !== "string") {
            return;
        }

        var origHandler = handleObj.handler,
                keys = handleObj.data.combi.toLowerCase().split(" "),
                disableInInput = handleObj.data.disableInInput;

        var sequences = [];
        jQuery.each(keys, function() {
            if (/;/.test(this)) {
                sequences.push(this.split(";"));
            }
        });

        handleObj.handler = function(event) {
            // Don't fire in text-accepting inputs that we didn't directly bind to
            if (disableInInput
                    && this !== event.target
                    && (   /textarea|select/i.test(event.target.nodeName)
                    || /text|password|search|tel|url|email|number/.test(event.target.type)
                    )
                    ) {
                return;
            }

            // Keypress represents characters, not special keys
            var special = event.type !== "keypress" && jQuery.hotkeys.specialKeys[ event.which ],
                    character = String.fromCharCode(event.which).toLowerCase(),
                    key, modif = "", possible = {};

            // check combinations (alt|ctrl|shift+anything)
            if (event.altKey && special !== "alt") {
                modif += "alt+";
            }

            if (event.ctrlKey && special !== "ctrl") {
                modif += "ctrl+";
            }

            // TODO: Need to make sure this works consistently across platforms
            if (event.metaKey && !event.ctrlKey && special !== "meta") {
                modif += "meta+";
            }

            if (event.shiftKey && special !== "shift") {
                modif += "shift+";
            }

            if (special) {
                possible[ modif + special ] = true;

            } else {
                possible[ modif + character ] = true;
                possible[ modif + jQuery.hotkeys.shiftNums[ character ] ] = true;

                // "$" can be triggered as "Shift+4" or "Shift+$" or just "$"
                if (modif === "shift+") {
                    possible[ jQuery.hotkeys.shiftNums[ character ] ] = true;
                }
            }

            for (var i = 0, l = keys.length; i < l; i++) {

                // check for sequence based shortcut
                for (var c = 0; c < sequences.length; c++) {

                    if (sequences[c][sequenceKeys.length] && possible[ sequences[c][sequenceKeys.length] ] && ( sequenceKeys.length == 0 || IsPartialSequence(sequenceKeys, sequences[c]) )) {
                        sequenceKeys.push(sequences[c][sequenceKeys.length]);

                        clearTimeout(sequenceTimeout);
                        sequenceTimeout = window.setTimeout(function() {
                            ClearSequence();
                        }, 1000);
                    }

                    if (sequenceKeys && SequencesEqual(sequenceKeys, sequences[c])) {
                        ClearSequence();
                        return origHandler.apply(this, arguments);
                    }
                }

                if (sequenceTimeout == null && possible[ keys[i] ]) {
                    return origHandler.apply(this, arguments);
                }
            }
        };

    }

    function ClearSequence() {
        window.setTimeout(function() {
            clearTimeout(sequenceTimeout);
            sequenceTimeout = null;
            sequenceKeys = [];
        }, 50);
    }

    function IsPartialSequence(partialSequence, sequence) {
        for (var i = 0, l = partialSequence.length; i < l; i++) {
            if (partialSequence[i] !== sequence[i]) {
                return false;
            }
        }
        return true;
    }

    function SequencesEqual(first, second) {
        if (! first || ! second || first.length != second.length) {
            return false;
        }

        for (var i = 0, l = second.length; i < l; i++) {
            if (first[i] !== second[i]) {
                return false;
            }
        }
        return true;
    }

    jQuery.each([ "keydown", "keyup", "keypress" ], function() {
        jQuery.event.special[ this ] = { add: keyHandler };
    });

})(jQuery);