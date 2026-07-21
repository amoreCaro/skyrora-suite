import { useBlockProps, RichText } from '@wordpress/block-editor';

const save = ({ attributes }) => {
  const {
    content,
    level,
    textAlign,
    color,
    fontWeight,
    fontSize,
    lineHeight,
    fontFamily,
    textTransform,
    paddingLeft,
    paddingRight,
    paddingTop,
    paddingBottom,
  } = attributes;

  const headingSize = (hLevel) => {
    switch (hLevel) {
      case 1: return '2.5rem';
      case 2: return '2.0rem';
      case 3: return '1.75rem';
      case 4: return '1.5rem';
      case 5: return '1.25rem';
      case 6: return '1.0rem';
      default: return '2rem';
    }
  };

  return (
    <>
      <style dangerouslySetInnerHTML={{
        __html: `
      @media (max-width: 768px) {
        .wp-heading {
          padding-left: 12px !important;
          padding-right: 12px !important;
        }
      }
    `
      }} />

      <div {...useBlockProps.save()}>
        <RichText.Content
          tagName={`h${level}`}
          value={content}
          className="wp-heading"
          style={{
            color,
            fontWeight,
            fontSize: headingSize(level),
            lineHeight,
            fontFamily,
            textTransform,
            textAlign,
            paddingTop: `${paddingTop}px`,
            paddingBottom: `${paddingBottom}px`,
            paddingLeft: `${paddingLeft}px`,
            paddingRight: `${paddingRight}px`,
            margin: "0px",
          }}
        />
      </div>
    </>
  );
};

export default save;
