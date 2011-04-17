/*
 * jQuery emoticons plug-in 0.5
 *
 * Copyright (c) 2009 Sebastian Kreft
 *
 * Licensed under the GPL license:
 * http://www.gnu.org/licenses/gpl.html
 *
 * Replaces occurrences of emoticons with the corresponding image
 * images are of class emoticonimg so they can be styled
 */
jQuery.fn.emoticons = function(icon_folder) {
    /* emoticons is the folder where the emoticons are stored*/
    var icon_folder = icon_folder || "emoticons";
    //var settings = jQuery.extend({emoticons: "emoticons"}, options);
    /* keys are the emoticons
     * values are the ways of writing the emoticon
     *
     * for each emoticons should be an image with filename
     * 'face-emoticon.png'
     * so for example, if we want to add a cow emoticon
     * we add "cow" : Array("(C)") to emotes
     * and an image called 'face-cow.png' under the emoticons folder   
     */
    var emotes = {"smile": Array(":-)",":)","=]","=)"),
                  "sad": Array(":-(","=(",":[",":&lt;"),
                  "wink": Array(";-)",";)",";]","*)"),
                  "grin": Array(":D","=D","XD","BD","8D","xD"),
                  "surprise": Array(":O","=O",":-O","=-O"),
                  "devilish": Array("(6)"),
                  "angel": Array("(A)"),
                  "crying": Array(":'(",":'-("),
                  "plain": Array(":|"),
                  "smile-big": Array(":o)"),
                  "glasses": Array("8)","8-)"),
                  "kiss": Array("(K)",":-*"),
                  "monkey": Array("(M)")};
                  
    /* Replaces all ocurrences of emoticons in the given html with images
     */
    function emoticons(html){
        for(var emoticon in emotes){
            for(var i = 0; i < emotes[emoticon].length; i++){
                /* css class of images is emoticonimg for styling them*/
                html = html.replace(emotes[emoticon][i],"<img src=\""+icon_folder+"/face-"+emoticon+".png\" class=\"emoticonimg\" alt=\""+emotes[emoticon][i]+"\"/>","g");
            }
        }
        return html;
    }
    return this.each(function(){
        $(this).html(emoticons($(this).html()));
    });
};