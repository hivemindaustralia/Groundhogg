
var HtmlBlock = {};

( function( $, editor, block ) {

    $.extend( block, {

        blockType: 'html',
        htmlCode: null,

        content: null,

        init : function () {
            this.content  = $( '#html-content' );
            var self = this;
            $(document).on( 'madeActive', function (e, block, blockType ) {
                if ( self.blockType === blockType ){
                    self.parse( block );
                }
            });
        },

        /**
         * A jquery implement block.
         *
         * @param block $
         */
        parse: function ( block ) {

            // console.log( {block: block} );

            if ( ! this.htmlCode ){

                this.htmlCode = CodeMirror.fromTextArea( document.getElementById("html-content"), {
                    lineNumbers: true,
                    lineWrapping: true,
                    mode: "text/html",
                    matchBrackets: true,
                    indentUnit: 4,
                    specialChars: /[\u0000-\u001f\u007f-\u009f\u00ad\u061c\u200b-\u200f\u2028\u2029\ufeff]/,
                });

                this.htmlCode.on( 'change', function ( cm ) {
                    editor.getActive().find('.content-inside').html( cm.getValue() );
                } );

            }
            this.htmlCode.setSize( this.content.parent().width(), this.content.parent().height() );
            this.htmlCode.setValue( block.find('.content-inside').html().trim() );
        }

    } );

    $(function(){
        block.init();
    })

})( jQuery, EmailEditor, HtmlBlock );