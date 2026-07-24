import { useBlockProps } from '@wordpress/block-editor';

const save = ({ attributes }) => {
	const {
		content,
		textAlign,
		color,
		backgroundColor,
		fontWeight,
		fontSize,
		lineHeight,
		fontFamily,
		textTransform,
		paddingLeft,
		paddingRight,
		paddingTop,
		paddingBottom,
		url
	} = attributes;

	return (
		<>
			<style
				dangerouslySetInnerHTML={{
					__html: `
						@media (max-width: 768px) {
							.wp-button {
								padding-left: 12px !important;
								padding-right: 12px !important;
							}
						}
					`
				}}
			/>
			<div
				{...useBlockProps.save({ className: 'wp-button' })}
				style={{
					backgroundColor: '#fff',
					paddingTop: `${paddingTop}px`,
					paddingBottom: `${paddingBottom}px`,
					paddingLeft: `${paddingLeft}px`,
					paddingRight: `${paddingRight}px`,
					display: 'flex',
					justifyContent:
						textAlign === 'center'
							? 'center'
							: textAlign === 'right'
							? 'flex-end'
							: 'flex-start',
				}}
			>
				<a
					href={url || '#'}
					style={{
						color,
						backgroundColor,
						fontWeight,
						fontSize,
						lineHeight,
						fontFamily,
						textTransform,
						border: 'none',
						cursor: 'pointer',
						display: 'inline-block',
						maxWidth: '256px',
						width: '100%',
						height: '56px',
						textDecoration: 'none',
						padding: '16px 24px',
						boxSizing: 'border-box',
						textAlign: 'center'
					}}
				>
					<span>{content}</span>
				</a>
			</div>
		</>
	);
};

export default save;
