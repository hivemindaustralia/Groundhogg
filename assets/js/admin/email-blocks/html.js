
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
            var self = this;

            if ( ! this.htmlCode ){

                var editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
                editorSettings.codemirror = _.extend(
                    {},
                    editorSettings.codemirror,
                    {
                        indentUnit: 4,
                        tabSize: 4
                    }
                );

                self.htmlCode = wp.codeEditor.initialize( $('#html-content'), editorSettings ).codemirror;
                // self.htmlCode = self.htmlCode.codemirror;

                this.htmlCode.on( 'change', function ( cm ) {
                    editor.getActive().find('.content-inside').html( cm.getValue() );
                } );

            }

            this.htmlCode.setSize( this.content.parent().width(), $( '#post-body' ).height() );
            this.htmlCode.setValue( html_beautify( block.find('.content-inside').html().trim(), {indent_with_tabs: true} ) );
        }

    } );

    $(function(){
        block.init();
    })

})( jQuery, EmailEditor, HtmlBlock );