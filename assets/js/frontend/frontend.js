(function (gh, $) {
    $.extend( gh, {
        previousFormImpressions: [],

        setCookie: function(cname, cvalue, exdays){
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },

        getCookie: function( cname ){
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return null;
        },

        pageView : function(){
            var self = this;

            if ( this.tracking_enabled ){

                $.ajax({
                    type: "post",
                    url: self.page_view_endpoint,
                    data: { ref: window.location.href, _ghnonce: self._ghnonce },
                    beforeSend: function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', self._wpnonce );
                    },
                    success: function( response ){},
                    error: function(){}
                });

            }
        },

        logFormImpressions : function() {
            var self = this;
            var forms = $( '.gh-form' );
            $.each( forms, function ( i, e ) {
                var fId = $(e).find( 'input[name="gh_submit_form"]' ).val();
                self.formImpression( fId );
            });
        },

        formImpression : function( id ){
            var self = this;

            if ( ! id ){
                return;
            }

            if ( this.previousFormImpressions.indexOf( id  ) !== -1 ){
                return;
            }

            $.ajax({
                type: "post",
                url: self.form_impression_endpoint,
                dataType: 'json',
                data: { ref: window.location.href, form_id: id, _ghnonce: self._ghnonce },
                beforeSend: function ( xhr ) {
                    xhr.setRequestHeader( 'X-WP-Nonce', self._wpnonce );
                },
                success: function( response ){
                    self.previousFormImpressions.push( [ id ] );
                    self.setCookie( self.cookies.form_impression, self.previousFormImpressions.join(), 3 )
                },
                error: function(){}
            });
        },

        init: function(){
            var referer = this.getCookie( this.cookies.lead_source );

            if ( ! referer ){
                this.setCookie( this.cookies.lead_source, document.referrer, 3 )
            }

            var previousFormImpressions = this.getCookie( this.cookies.form_impressions );

            if ( ! previousFormImpressions ){
                previousFormImpressions = '';
            }

            this.previousFormImpressions = previousFormImpressions.split( ',' );

            this.pageView();
            this.logFormImpressions();
        }
    } );

    $(function(){
        gh.init();
    });

})(Groundhogg, jQuery);

