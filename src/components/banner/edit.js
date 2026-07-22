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

const BannerEdit = ({ attributes, setAttributes }) => {
	const {
		bannerUrl,
		bannerId,
		paddingLeft,
		paddingRight,
		paddingTop,
		paddingBottom
	} = attributes;

	const bannerWrapper = {
		paddingTop: `${paddingTop}px`,
		paddingBottom: `${paddingBottom}px`,
		paddingLeft: `${paddingLeft}px`,
		paddingRight: `${paddingRight}px`,
		width: "100%",
		boxSizing: "border-box",
		height: "244px",
		margin: "0",
		background: '#fff'
	};

	const banner = {
		display: "block",
		height: "244px",
		width: "100%",
		objectFit: "cover",
	};

	const mediaButton = {
		display: 'block',
		width: '100%',
		height: '244px',
		padding: 0,
		border: 0,
		background: 'transparent',
		cursor: 'pointer'
	};

	const imagePlaceholder = {
		display: 'flex',
		flexDirection: 'column',
		alignItems: 'center',
		justifyContent: 'center',
		gap: '12px',
		width: '100%',
		height: '244px',
		border: '2px dashed #a7aaad',
		borderRadius: '4px',
		background: '#f6f7f7',
		color: '#50575e',
		boxSizing: 'border-box'
	};

	const onSelectBanner = (media) => {
		setAttributes({ bannerUrl: media.url, bannerId: media.id });
	};

	const onRemoveBanner = () => {
		setAttributes({ bannerUrl: '', bannerId: 0 });
	};

	return (
		<>
			<BlockControls>
				{ bannerUrl && (
					<Button
						variant="link"
						onClick={ onRemoveBanner }
						label={ __( 'Remove Banner' ) }
						className="remove-banner-button"
						aria-label={ __( 'Remove Banner' ) }
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
				<PanelBody title={ __( 'Banner Settings', 'custom-banner' ) }>
					<TextControl
						label={ __( 'Padding Top (px)', 'custom-banner' ) }
						type="number"
						value={ paddingTop }
						onChange={ (val) => setAttributes({ paddingTop: parseInt(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Bottom (px)', 'custom-banner' ) }
						type="number"
						value={ paddingBottom }
						onChange={ (val) => setAttributes({ paddingBottom: parseInt(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Left (px)', 'custom-banner' ) }
						type="number"
						value={ paddingLeft }
						onChange={ (val) => setAttributes({ paddingLeft: parseFloat(val) }) }
					/>
					<TextControl
						label={ __( 'Padding Right (px)', 'custom-banner' ) }
						type="number"
						value={ paddingRight }
						onChange={ (val) => setAttributes({ paddingRight: parseFloat(val) }) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				<MediaUploadCheck>
					<div style={ bannerWrapper } className="wp-block wp-banner">
						{ bannerUrl ? (
							<img
								src={ bannerUrl }
								style={ banner }
								className="selected-banner"
								alt={ __( 'Selected Banner', 'custom-banner' ) }
							/>
						) : (
							<MediaUpload
								onSelect={ onSelectBanner }
								allowedTypes={ [ 'image' ] }
								value={ bannerId }
								render={ ( { open } ) => (
									<button
										type="button"
										onClick={ open }
										style={ mediaButton }
										className="select-banner-button"
										aria-label={ __( 'Select banner image', 'custom-banner' ) }
									>
										<span style={ imagePlaceholder }>
											<svg
												width="40"
												height="40"
												viewBox="0 0 24 24"
												fill="none"
												aria-hidden="true"
											>
												<rect
													x="3"
													y="4"
													width="18"
													height="16"
													rx="2"
													stroke="currentColor"
													strokeWidth="1.5"
												/>
												<circle
													cx="8.5"
													cy="9"
													r="1.5"
													fill="currentColor"
												/>
												<path
													d="M4 17l4.5-4.5 3 3 2-2L20 20"
													stroke="currentColor"
													strokeWidth="1.5"
													strokeLinecap="round"
													strokeLinejoin="round"
												/>
											</svg>
											<span>{ __( 'Select banner image', 'custom-banner' ) }</span>
										</span>
									</button>
								) }
							/>
						) }
					</div>
				</MediaUploadCheck>
			</div>
		</>
	);
};

export default BannerEdit;
