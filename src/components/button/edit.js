import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	BlockControls,
	AlignmentToolbar,
	PanelColorSettings
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	TabPanel,
	Button,
	ButtonGroup
} from '@wordpress/components';
import { alignLeft, alignCenter, alignRight } from '@wordpress/icons';

export default function Edit({ attributes, setAttributes }) {
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
		tabSelected,
		url
	} = attributes;

	const handlePaddingChange = (value, attribute) => {
		const valid = !isNaN(value) && value !== '' ? parseFloat(value) : 0;
		setAttributes({ [attribute]: valid });
	};

	const blockStyle = {
		backgroundColor: '#fff',
		display: 'flex',
		justifyContent:
			textAlign === 'center' ? 'center' :
				textAlign === 'right' ? 'flex-end' :
					'flex-start',
		textAlign,
		paddingTop: `${paddingTop}px`,
		paddingBottom: `${paddingBottom}px`,
		paddingLeft: `${paddingLeft}px`,
		paddingRight: `${paddingRight}px`,
	};

	return (
		<>
			<BlockControls>
				{/* Контейнер для AlignmentToolbar, який розтягується на всю ширину */}
				<div className="custom-alignment-toolbar-wrapper">
					<AlignmentToolbar
						value={textAlign}
						onChange={(newAlign) => setAttributes({ textAlign: newAlign || 'center' })}
					/>
				</div>
			</BlockControls>

			<InspectorControls>
			<div
                    style={{
                        width: '100%',
                    }}
                >
                    <TabPanel
                        className="my-tab-panel"
                        initialTabName={tabSelected || 'settings'}
                        onSelect={(tabName) => setAttributes({ tabSelected: tabName })}
                        tabs={[
                            { name: 'settings', title: <span className="dashicons dashicons-admin-generic" /> },
                            { name: 'styles', title: <span className="dashicons dashicons-admin-appearance" /> },
                        ]}
                    >
					{(tab) => {
						if (tab.name === 'settings') {
							return (
								<PanelBody title={__('Typography Settings', 'app')} initialOpen={true}>
									<RichText
										tagName="div"
										value={content}
										onChange={(value) => setAttributes({ content: value })}
										placeholder={__('Enter button text', 'app')}
										style={{
											padding: '10px 14px',
											border: '1px solid #ccc',
											borderRadius: '2px',
											marginBottom: '12px',
											width: '100%',
										}}
									/>

									<TextControl
										label={__('Button Link', 'app')}
										value={url}
										onChange={(value) => setAttributes({ url: value })}
										placeholder={__('Enter button URL', 'app')}
									/>
								</PanelBody>
							);
						}

						if (tab.name === 'styles') {
							return (
								<>
									<PanelColorSettings
										title={__('Colors', 'app')}
										initialOpen={true}
										colorSettings={[
											{
												value: color,
												onChange: (value) => setAttributes({ color: value }),
												label: __('Text Color', 'app')
											},
											{
												value: backgroundColor,
												onChange: (value) => setAttributes({ backgroundColor: value }),
												label: __('Background Color', 'app')
											}
										]}
									/>

									<PanelBody title={__('Typography', 'app')} initialOpen={true}>
										<ButtonGroup
											style={{
												width: '100%',
												display: 'flex',
												justifyContent: 'space-between',
												marginBottom: '16px'
											}}
										>
											<Button
												icon={alignLeft}
												label={__('Align Left', 'app')}
												isPressed={textAlign === 'left'}
												onClick={() => setAttributes({ textAlign: 'left' })}
												style={{ flex: 1 }}
											/>
											<Button
												icon={alignCenter}
												label={__('Align Center', 'app')}
												isPressed={textAlign === 'center'}
												onClick={() => setAttributes({ textAlign: 'center' })}
												style={{ flex: 1 }}
											/>
											<Button
												icon={alignRight}
												label={__('Align Right', 'app')}
												isPressed={textAlign === 'right'}
												onClick={() => setAttributes({ textAlign: 'right' })}
												style={{ flex: 1 }}
											/>
										</ButtonGroup>


										<TextControl
											label={__('Font Size', 'app')}
											value={fontSize}
											onChange={(value) => setAttributes({ fontSize: value })}
										/>
										<TextControl
											label={__('Line Height', 'app')}
											value={lineHeight}
											onChange={(value) => setAttributes({ lineHeight: value })}
										/>
										<SelectControl
											label={__('Font Weight', 'app')}
											value={fontWeight}
											options={[
												{ label: 'Normal', value: '400' },
												{ label: 'Medium', value: '500' },
												{ label: 'Semi-Bold', value: '600' },
												{ label: 'Bold', value: '700' }
											]}
											onChange={(value) => setAttributes({ fontWeight: value })}
										/>
										<SelectControl
											label={__('Text Transform', 'app')}
											value={textTransform}
											options={[
												{ label: 'None', value: 'none' },
												{ label: 'Uppercase', value: 'uppercase' },
												{ label: 'Lowercase', value: 'lowercase' },
												{ label: 'Capitalize', value: 'capitalize' }
											]}
											onChange={(value) => setAttributes({ textTransform: value })}
										/>
									</PanelBody>

									<PanelBody title={__('Spacing', 'app')} initialOpen={true}>
										<TextControl
											label={__('Padding Top (px)', 'app')}
											value={paddingTop}
											onChange={(value) => handlePaddingChange(value, 'paddingTop')}
										/>
										<TextControl
											label={__('Padding Bottom (px)', 'app')}
											value={paddingBottom}
											onChange={(value) => handlePaddingChange(value, 'paddingBottom')}
										/>
										<TextControl
											label={__('Padding Left (px)', 'app')}
											value={paddingLeft}
											onChange={(value) => handlePaddingChange(value, 'paddingLeft')}
										/>
										<TextControl
											label={__('Padding Right (px)', 'app')}
											value={paddingRight}
											onChange={(value) => handlePaddingChange(value, 'paddingRight')}
										/>
									</PanelBody>
								</>
							);
						}

						return null;
					}}
				</TabPanel>
				</div>
			</InspectorControls>
			<style
			dangerouslySetInnerHTML={{
				__html: `
				.my-tab-panel .components-tab-panel__tabs {
					display: flex !important;
					width: 100% !important;
					padding: 0 !important;
					margin: 0 !important;
				}
				.my-tab-panel .components-tab-panel__tabs button {
					flex: 1 1 50% !important;
					display: flex !important;
					justify-content: center !important;
					align-items: center !important;
					height: 40px !important;
					border-radius: 0 !important;
					padding: 0 !important;
				}
				@media (max-width: 768px) {
					.wp-block.wp-button {
					padding-left: 12px !important;
					padding-right: 12px !important;
					}
				}
				`,
			}}
			/>

			<div
				{...useBlockProps({
					className: 'wp-button',
					style: {
						...blockStyle,
						color,
						backgroundColor: '#fff',
					
					}
				})}
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
						paddingTop: `${paddingTop}px`,
						paddingBottom: `${paddingBottom}px`,
						paddingLeft: `${paddingLeft}px`,
						paddingRight: `${paddingRight}px`,
						boxSizing: 'border-box',
						textAlign: 'center'
					}}
				>
					<RichText
						tagName="span"
						value={content}
						onChange={(value) => setAttributes({ content: value })}
						placeholder={__('Enter button text', 'app')}
					/>
				</a>
			</div>
		</>
	);
}
