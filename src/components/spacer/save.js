import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
    const { backgroundColor, height, paddingLeft, paddingRight, paddingTop, paddingBottom } = attributes;

    return (
        <>
            <style dangerouslySetInnerHTML={{
                __html: `
                @media (max-width: 768px) {
                    .wp-spacer {
                        padding-left: 12px !important;
                        padding-right: 12px !important;
                    }
                }
            `}} />

            <div
                {...useBlockProps.save()}
                className="wp-block wp-spacer"
                style={{
                    backgroundColor: backgroundColor,
                    height: height,
                    paddingTop: `${paddingTop}px`,
                    paddingBottom: `${paddingBottom}px`,
                    paddingLeft: `${paddingLeft}px`,
                    paddingRight: `${paddingRight}px`,
                }}
            >
            </div>
        </>
    );
};

export default Save;
