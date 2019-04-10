(function ( e, ep, fields, $ ) {

    var GroundhoggIntegration = {
        fields: fields,

        getName: function getName() {
            return 'groundhogg_v2';
        },

        onElementChange: function onElementChange( setting ) {
            var self = this;
            if ( setting.indexOf( "groundhogg_v2" ) !== -1 ){
                self.updateFieldsMapping();
            }
        },

        updateFieldsMapping: function updateFieldsMapping() {
            this.getEditorControlView( 'groundhogg_v2_fields_map' ).updateMap();
        },
    };

    GroundhoggIntegration = Object.assign( ep.modules.forms.activecampaign, GroundhoggIntegration );
    ep.modules.forms.groundhogg = GroundhoggIntegration;

    /**
     * Gh Fields Map.
     */
    var GhFieldsMap = elementor.modules.controls.Fields_map.extend({

        updateMap: function updateMap() {
            var self = this,
                savedMapObject = {};

            self.collection.each(function (model) {
                savedMapObject[model.get('local_id')] = model.get('remote_id');
            });

            self.collection.reset();

            var fields = self.elementSettingsModel.get('form_fields').models;

            _.each(fields, function (field) {

                var model = {
                    local_id : field.get( 'custom_id' ),
                    local_label : field.get( 'field_label' ),
                    remote_id: savedMapObject[field.get( 'custom_id' )] ? savedMapObject[ field.get( 'custom_id' ) ] : ''
                };

                self.collection.add( model );
            });

            self.render();
        },

        getFieldOptions()
        {
            return ep.modules.forms.groundhogg.fields;
        },

        onRender: function onRender() {

            e.modules.controls.Base.prototype.onRender.apply(this, arguments);

            var self = this;

            self.children.each(function (view) {

                var localFieldsControl = view.children.last(),
                    options = {
                        '': '- ' + elementor.translate( 'None' ) + ' -'
                    },
                    label = view.model.get( 'local_label' );

                _.each( self.getFieldOptions(), function (model, index ) {
                    // console.log( model );
                    options[ model.remote_id ] = model.remote_label || 'Field #' + (index + 1);
                });

                localFieldsControl.model.set( 'label', label );
                localFieldsControl.model.set( 'options', options );
                localFieldsControl.render();

                view.$el.find('.elementor-repeater-row-tools').hide();
                view.$el.find('.elementor-repeater-row-controls').removeClass('elementor-repeater-row-controls').find('.elementor-control').css({
                    padding: '10px 0'
                });
            });

            self.$el.find('.elementor-button-wrapper').remove();

            if (self.children.length) {
                self.$el.show();
            }
        }
    });

    e.addControlView( 'Gh_fields_map', GhFieldsMap );

})( elementor, elementorPro, ghMappableFields.fields, jQuery );