import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	BlockControls,
	PanelColorSettings,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	TabPanel,
} from '@wordpress/components';

const HeadingEdit = ({ attributes, setAttributes }) => {
	const {
		content,
		level,
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

	const onChangePadding = (side, value) => {
		setAttributes({ [side]: parseFloat(value || 0) });
	};

	const blockProps = useBlockProps({
		className: 'custom-heading',
		style: {
			color,
			backgroundColor: backgroundColor || '#FFFFFF',
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
			margin: '0px',
		},
	});

	return (
		<>
			<BlockControls />
			<InspectorControls>
				{/* Wrap TabPanel in a div with inline style to ensure full width */}
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
									<PanelBody title={__('Heading Settings', 'custom-heading')}>
										<RichText
											tagName="div"
											value={content}
											onChange={(value) => setAttributes({ content: value })}
											placeholder={__('Enter heading', 'app')}
											style={{
												padding: '10px 14px',
												border: '1px solid #ccc',
												borderRadius: '2px',
												marginBottom: '12px',
												width: '100%',
											}}
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
													label: __('Text Color', 'app'),
												},
												{
													value: backgroundColor,
													onChange: (value) => setAttributes({ backgroundColor: value }),
													label: __('Background Color', 'app'),
												},
											]}
										/>
										<PanelBody title={__('Style Settings', 'custom-heading')}>
											<SelectControl
												label={__('Heading Level', 'custom-heading')}
												value={level}
												options={[
													{ label: 'H1', value: 1 },
													{ label: 'H2', value: 2 },
													{ label: 'H3', value: 3 },
													{ label: 'H4', value: 4 },
													{ label: 'H5', value: 5 },
													{ label: 'H6', value: 6 },
												]}
												onChange={(val) => setAttributes({ level: parseInt(val, 10) })}
											/>

											<TextControl
												label={__('Font Size (rem)', 'custom-heading')}
												type="number"
												value={(fontSize || '').replace('rem', '')}
												onChange={(val) => setAttributes({ fontSize: `${val}rem` })}
											/>
											<TextControl
												label={__('Line Height', 'custom-heading')}
												type="number"
												step="0.1"
												value={lineHeight}
												onChange={(val) => setAttributes({ lineHeight: val })}
											/>
											<SelectControl
												label={__('Font Weight', 'custom-heading')}
												value={fontWeight}
												options={[
													{ label: 'Normal', value: '400' },
													{ label: 'Bold', value: '700' },
													{ label: 'Bolder', value: '900' },
												]}
												onChange={(val) => setAttributes({ fontWeight: val })}
											/>
											<SelectControl
												label={__('Text Transform', 'custom-heading')}
												value={textTransform}
												options={[
													{ label: 'None', value: 'none' },
													{ label: 'Uppercase', value: 'uppercase' },
													{ label: 'Lowercase', value: 'lowercase' },
													{ label: 'Capitalize', value: 'capitalize' },
												]}
												onChange={(val) => setAttributes({ textTransform: val })}
											/>
											<TextControl
												label={__('Padding Top (px)', 'custom-heading')}
												value={paddingTop}
												onChange={(value) => onChangePadding('paddingTop', value)}
											/>
											<TextControl
												label={__('Padding Bottom (px)', 'custom-heading')}
												value={paddingBottom}
												onChange={(value) => onChangePadding('paddingBottom', value)}
											/>
											<TextControl
												label={__('Padding Left (px)', 'custom-heading')}
												value={paddingLeft}
												onChange={(value) => onChangePadding('paddingLeft', value)}
											/>
											<TextControl
												label={__('Padding Right (px)', 'custom-heading')}
												value={paddingRight}
												onChange={(value) => onChangePadding('paddingRight', value)}
											/>
										</PanelBody>
									</>
								);
							}

							return null;
						}}
					</TabPanel>
				</div>

				{/* Inline style override for TabPanel tab buttons */}
				<style>{`
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
				`}</style>
			</InspectorControls>

			<RichText
				{...blockProps}
				tagName={`h${level}`}
				value={content}
				onChange={(value) => setAttributes({ content: value })}
				placeholder={__('Enter heading text', 'custom-heading')}
			/>
		</>
	);
};

export default HeadingEdit;
