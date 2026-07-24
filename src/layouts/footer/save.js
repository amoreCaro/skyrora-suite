import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const {
        imageItems = [],
        footerLinks = [],
        copyright,
        copyrightUrl,
        paddingLeft,
        paddingRight,
    } = attributes;

    const blockProps = useBlockProps.save();

    return (
        <div
            {...blockProps}
            style={{
                width: '100%',
                boxSizing: 'border-box',
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'center',
                alignItems: 'center',
                background: '#181B24',
                paddingTop: '30px',
                paddingBottom: '48px',
                paddingLeft: `${paddingLeft}px`,
                paddingRight: `${paddingRight}px`,
            }}
        >
            {/* Footer Socials */}
            <div
                style={{
                    display: 'flex',
                    flexWrap: 'wrap',
                    justifyContent: 'center',
                    gap: '20px',
                    width: '100%',
                }}
            >
                {imageItems.map((item, index) => (
                    <a
                        key={index}
                        href={item.imgLink || '#'}
                        style={{
                            maxWidth: '48px',
                            width: '100%',
                            height: '48px',
                        }}
                    >
                        {item.imgUrl && (
                            <img
                                src={item.imgUrl}
                                alt={`Social Icon ${index + 1}`}
                                style={{
                                    width: '100%',
                                    height: '100%',
                                    objectFit: 'cover',
                                }}
                            />
                        )}
                    </a>
                ))}
            </div>

            {/* Footer Links */}
            <div className="footer-links" style={{ display: 'flex', gap: '20px', margin: '16px 0px', flexWrap: 'wrap', justifyContent: 'center' }}>
                {footerLinks.map((link, index) => (
                    <a
                        key={index}
                        href={link.url}
                        style={{
                            fontSize: '12px',
                            lineHeight: '100%',
                            color: '#FFFFFF',
                            textAlign: 'center',
                            textTransform: 'uppercase',
                            fontFamily: 'Bai Jamjuree, sans-serif',
                            textDecoration: 'none',
                        }}
                    >
                        {link.text}
                    </a>
                ))}
            </div>

            {/* Copyright */}
            <a href={copyrightUrl}>
                <span style={{
                    fontFamily: 'Bai Jamjuree, sans-serif',
                    fontWeight: 400,
                    fontSize: '12px',
                    lineHeight: '100%',
                    textAlign: 'center',
                    textTransform: 'uppercase',
                    color: "#B8BDCC",
                    display: 'inline-block'
                }}>
                    {copyright}
                </span>
            </a>
        </div>
    );
}
