import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const {
        id,
        text1,
        text2,
        paddingLeft,
        paddingRight,
        paddingTop,
        paddingBottom,
        fontFamily,
        fontSize,
        lineHeight,
        color,
        fontWeight,
        textTransform,
        backgroundColor,
        textAlign,
    } = attributes;

    const blockProps = useBlockProps.save();

    return (
        <div
            id={id}
            {...blockProps}
            style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                backgroundColor: backgroundColor || '#181b24',
                paddingLeft: `${paddingLeft}px`,
                paddingRight: `${paddingRight}px`,
                paddingTop: `${paddingTop}px`,
                paddingBottom: `${paddingBottom}px`,
            }}
        >
            {/* Logo/Image Section */}
            <div style={{ maxWidth: '114px', height: '60px', width: '100%' }}>
                <svg width="1em" height="1em" className="icon icon-logo">
                    <use xlinkHref="/wp-content/plugins/skyrora-suite/assets/dist/images/symbol-defs.svg#icon-logo" />
                </svg>
            </div>

            {/* Text Section */}
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'flex-end',
                    marginLeft: '12px',
                    borderRight: '2px solid #164BDC',
                    paddingRight: '12px',
                    height: '32px',
                    textAlign: textAlign || 'left',
                }}
            >
                {text1 && (
                    <RichText.Content
                        tagName="p"
                        value={text1}
                        style={{
                            margin: '0 0 8px 0',
                            color,
                            fontFamily,
                            fontSize: `${fontSize}px`,
                            lineHeight: `${lineHeight}px`,
                            fontWeight,
                            textTransform,
                        }}
                    />
                )}
                {text2 && (
                    <RichText.Content
                        tagName="p"
                        value={text2}
                        style={{
                            margin: 0,
                            color,
                            fontFamily,
                            fontSize: `${fontSize}px`,
                            lineHeight: `${lineHeight}px`,
                            fontWeight,
                            textTransform,
                        }}
                    />
                )}
            </div>
        </div>
    );
}
