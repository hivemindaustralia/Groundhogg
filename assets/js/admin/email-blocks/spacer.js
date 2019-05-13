
var SpacerBlock = {};

( function( $, editor, block ) {

    $.extend( block, {

        blockType: 'spacer',
        height: null,
        init : function () {

            this.height  = $( '#spacer-size' );
            this.height.on( 'change input', function ( e ) {
                editor.getActive().find('.spacer').attr('height', $(this).val() );
            });

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

            this.height.val( block.find('.spacer').height() );

        }

    } );

    $(function(){
        block.init();
    })

})( jQuery, EmailEditor, SpacerBlock );