import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
    const { id, align, style, color, paddingLeft, paddingRight } = attributes;

    return (
        <>
            <style dangerouslySetInnerHTML={{
                __html: `
                    @media (max-width: 768px) {
                        .wp-wrapper {
                            padding-left: 12px !important;
                            padding-right: 12px !important;
                        }
                    }
                `
            }} />
            <div
                className="wp-wrapper"
                style={{
                    marginTop: '16px',
                    marginBottom: '16px',
                    paddingLeft: `${paddingLeft}px`,
                    paddingRight: `${paddingRight}px`,
                    boxSizing: 'border-box',
                    width: '100%',
                    maxWidth: '100%',
                }}
            >
                <hr
                    {...useBlockProps.save()}
                    id={id}
                    style={{
                        textAlign: align,
                        borderStyle: style,
                        borderColor: color,
                        borderWidth: '1px',
                    }}
                />
            </div>
        </>
    );
};

export default Save;
