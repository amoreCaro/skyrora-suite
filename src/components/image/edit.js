import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
	BlockControls
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl
} from '@wordpress/components';

const ImageEdit = ({ attributes, setAttributes }) => {
	const {
		imageUrl,
		imageId,
		paddingLeft,
		paddingRight,
		paddingTop,
		paddingBottom
	} = attributes;

	const imageWrapper = {
		paddingTop: `${paddingTop}px`,
		paddingBottom: `${paddingBottom}px`,
		paddingLeft: `${paddingLeft}px`,
		paddingRight: `${paddingRight}px`,
		width: "auto",
		margin: "0",
		background: '#fff'
	};

	const image = {
		height: "auto",
		objectFit: "cover",
		display: "block",
		width: "100%",
	};

	const onSelectImage = (media) => {
		setAttributes({ imageUrl: media.url, imageId: media.id });
	};

	const onRemoveImage = () => {
		setAttributes({ imageUrl: '', imageId: 0 });
	};

	return (
		<>
			<BlockControls>
				{ imageUrl && (
					<Button
						variant="link"
						onClick={ onRemoveImage }
						label={ __( 'Remove Image' ) }
						className="remove-image-button"
						aria-label={ __( 'Remove Image' ) }
					>
						<svg
							width="20"
							height="20"
							viewBox="0 0 24 24"
							fill="none"
							xmlns="http://www.w3.org/2000/svg"
						>
							<path
								d="M18 6L6 18M6 6l12 12"
								stroke="black"
								strokeWidth="2"
								strokeLinecap="round"
							/>
						</svg>
					</Button>
				) }
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Image Settings', 'custom-image' ) }>
					<TextControl
						label={ __( 'Padding Top (px)', 'custom-image' ) }
						type="number"
						value={ paddingTop }
						onChange={ (val) => setAttributes({ paddingTop: parseInt(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Bottom (px)', 'custom-image' ) }
						type="number"
						value={ paddingBottom }
						onChange={ (val) => setAttributes({ paddingBottom: parseInt(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Left (px)', 'custom-image' ) }
						type="number"
						value={ paddingLeft }
						onChange={ (val) => setAttributes({ paddingLeft: parseFloat(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Right (px)', 'custom-image' ) }
						type="number"
						value={ paddingRight }
						onChange={ (val) => setAttributes({ paddingRight: parseFloat(val) }) }
					/>
				</PanelBody>
			</InspectorControls>

			<style
				dangerouslySetInnerHTML={ {
					__html: `
						@media (max-width: 768px) {
							.wp-block .wp-image {
								padding-left: 12px !important;
								padding-right: 12px !important;
								padding-top: 16px !important;
								padding-bottom: 16px !important;
							}
						}
					`,
				} }
			/>

			<div { ...useBlockProps() }>
				<MediaUploadCheck>
					<div style={ imageWrapper } className="wp-block wp-image">
						{ imageUrl ? (
							<img
								src={ imageUrl }
								style={ image }
								className="selected-image"
								alt={ __( 'Selected Image', 'custom-image' ) }
							/>
						) : (
							<MediaUpload
								onSelect={ onSelectImage }
								allowedTypes={ [ 'image' ] }
								render={ ( { open } ) => (
									<Button onClick={ open } className="select-image-button" variant="primary">
										{ __( 'Select Image', 'custom-image' ) }
									</Button>
								) }
							/>
						) }
					</div>
				</MediaUploadCheck>
			</div>
		</>
	);
};

export default ImageEdit;
