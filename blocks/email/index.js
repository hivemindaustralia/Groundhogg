/**
 * Block dependencies
 */
import classnames from 'classnames';
// import icons from './icons';
// import './style.scss';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const {
    registerBlockType,
} = wp.blocks;
const {
    RichText,
    // AlignmentToolbar,
    // BlockControls,
    // BlockAlignmentToolbar,
    InspectorControls,
    ColorPalette,
} = wp.editor;
const {
    PanelBody,
    PanelRow,
    PanelColor,
    FormToggle,
    ToggleControl,
    RangeControl,
    TextControl,
} = wp.components;


/**
  * Register block
 */
export default registerBlockType(
    'groundhogg/email',
    {
        title: __( 'Email', 'groundhogg' ),
        description: __( 'Collect the email of a lead.', 'groundhogg'),
        category: 'groundhogg',
        icon: 'feedback',
        keywords: [
            __( 'Last', 'groundhogg' ),
            __( 'Name', 'groundhogg' ),
            __( 'Form', 'groundhogg' ),
        ],
        attributes: {

            label: {
                type: 'array',
                source: 'children',
                selector: 'label',
                default: __( 'Email *' , 'groundhogg' )
            },
            showLabel: {
                type: 'boolean',
                default: true,
                selector: 'label'
            },
            placeholder: {
                type: 'string',
                default: __( 'Email...', 'groundhogg' ),
                selector: 'input',
                source: 'attribute',
                attribute: 'placeholder'
            },
            width: {
                type: 'string',
                default: '80'
            },
            fontSize: {
                type: 'string',
                default: '16'
            },
            borderColor: {
                type: 'string',
                default: '#444444'
            },
            backgroundColor: {
                type: 'string',
                default: '#f1f1f1'
            },
            textColor: {
                type: 'string',
                default: '#000000'
            }
        },
        edit: props => {
            const { attributes: { label, required, showLabel, placeholder, width, fontSize, borderColor, backgroundColor, textColor },
                className, setAttributes } = props;

            return [
                <InspectorControls>
                    <PanelBody
                        title={ __( 'Field Options', 'groundhogg' ) }
                    >
                        <PanelBody>
                            <ToggleControl
                                label={ __( 'Show Label', 'groundhogg' ) }
                                checked={ showLabel }
                                onChange={ showLabel => setAttributes( { showLabel } ) }
                                help={ __( 'Toggles the outside field label.', 'groundhogg' ) }
                            />
                        </PanelBody>
                        <PanelBody>
                            <TextControl
                                label={ __( 'Placeholder Text', 'groundhogg' ) }
                                help={ __( 'Input placeholder text.', 'groundhogg' ) }
                                value={ placeholder }
                                onChange={ placeholder => setAttributes( { placeholder } ) }
                            />
                        </PanelBody>
                        <PanelBody>
                            <RangeControl
                                beforeIcon="arrow-left-alt2"
                                afterIcon="arrow-right-alt2"
                                label={ __( 'Field Width', 'groundhogg' ) }
                                value={ width }
                                onChange={ width => setAttributes( { width } ) }
                                min={ 20 }
                                max={ 100 }
                            />
                        </PanelBody>
                        <PanelBody>
                            <RangeControl
                                beforeIcon="arrow-left-alt2"
                                afterIcon="arrow-right-alt2"
                                label={ __( 'Font Size', 'groundhogg' ) }
                                value={ fontSize }
                                onChange={ fontSize => setAttributes( { fontSize } ) }
                                min={ 10 }
                                max={ 32 }
                            />
                        </PanelBody>
                        <PanelColor
                            title={ __( 'Font Color', 'groundhogg' ) }
                            colorValue={ textColor }
                        >
                            <ColorPalette
                                value={ textColor }
                                onChange={ textColor => setAttributes( { textColor } ) }
                            />
                        </PanelColor>
                        <PanelColor
                            title={ __( 'Border Color', 'groundhogg' ) }
                            colorValue={ borderColor }
                        >
                            <ColorPalette
                                value={ borderColor }
                                onChange={ borderColor => setAttributes( { borderColor } ) }
                            />
                        </PanelColor>
                        <PanelColor
                            title={ __( 'Background Color', 'groundhogg' ) }
                            colorValue={ backgroundColor }
                        >
                            <ColorPalette
                                value={ backgroundColor }
                                onChange={ backgroundColor => setAttributes( { backgroundColor } ) }
                            />
                        </PanelColor>
                    </PanelBody>
                </InspectorControls>,
                <div
                    className={ className }
                >
                    {
                        showLabel &&  <RichText
                            tagName="label"
                            placeholder={ __( 'Email...' ) }
                            value={ label }
                            onChange={ ( label ) => props.setAttributes( { label } ) }
                        />
                    }

                    <input
                        type="text"
                        className='gh-input gh-email'
                        placeholder={placeholder}
                        style={ {
                            width: width + '%',
                            fontSize: fontSize + 'px',
                            color: textColor,
                            borderColor: borderColor,
                            backgroundColor: backgroundColor,
                        } }
                    />
                </div>
            ];
        },
        save: props => {

            const { attributes: { label, showLabel, placeholder, width, fontSize, borderColor, backgroundColor, textColor },
                className, setAttributes } = props;

            return (
                <div
                    className={ classnames( className, 'gh-form-field' ) }
                >
                    <p>
                        {
                            showLabel &&  <div className="gh-input-label-container">
                                <label htmlFor="gh-email" className="gh-input-label gh-email-label">{label}</label>
                            </div>
                        }
                        <div className="gh-input-container">
                            <input
                                type="email"
                                id="gh-email"
                                name="email"
                                className={ classnames( 'gh-input', 'gh-email', 'required' ) }
                                placeholder={ placeholder }
                                style={ {
                                    width: width + '%',
                                    fontSize: fontSize + 'px',
                                    color: textColor,
                                    borderColor: borderColor,
                                    backgroundColor: backgroundColor,
                                } }
                            />
                        </div>
                    </p>
                </div>
            );
        },

    },
);
