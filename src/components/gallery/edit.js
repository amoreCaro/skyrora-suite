import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	MediaUpload,
	MediaUploadCheck,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	Button,
} from '@wordpress/components';
import { plus, edit, trash } from '@wordpress/icons';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

const GalleryEdit = ({ attributes, setAttributes }) => {
	const {
		galleryImages = [],
		paddingTop = 0,
		paddingBottom = 0,
		paddingLeft = 0,
		paddingRight = 0,
		backgroundColor = '#ffffff',
	} = attributes;

	// Replace all gallery images
	const handleUpdateGallery = (mediaArray) => {
		const newImages = mediaArray.map((img) => ({
			imgUrl: img.url,
			imgId: img.id,
			imgLink: img.link || '',
		}));
		setAttributes({ galleryImages: newImages });
	};

	// Add more images to existing gallery
	const addToGallery = (mediaArray) => {
		const newImages = mediaArray.map((img) => ({
			imgUrl: img.url,
			imgId: img.id,
			imgLink: img.link || '',
		}));
		setAttributes({ galleryImages: [...galleryImages, ...newImages] });
	};

	// Delete an image at given index
	const deleteImage = (index) => {
		const updated = galleryImages.filter((_, i) => i !== index);
		setAttributes({ galleryImages: updated });
	};

	// Handle drag and drop reorder
	const onDragEnd = (result) => {
		if (!result.destination) return;
		const reordered = Array.from(galleryImages);
		const [moved] = reordered.splice(result.source.index, 1);
		reordered.splice(result.destination.index, 0, moved);
		setAttributes({ galleryImages: reordered });
	};

	// Style for gallery container
	const galleryStyle = {
		paddingTop: `${paddingTop}px`,
		paddingBottom: `${paddingBottom}px`,
		paddingLeft: `${paddingLeft}px`,
		paddingRight: `${paddingRight}px`,
		backgroundColor,
		display: 'flex',
		flexDirection: 'column',
		gap: '8px',
	};

	// Render the gallery images in rows: first 2 images in one row, then next in rows of 3
	const renderRows = () => {
		if (!galleryImages.length) {
			return null;
		}

		const rows = [];
		// First row: 2 images
		rows.push(
			<div
				key="row-0"
				style={{
					display: 'grid',
					gridTemplateColumns: 'repeat(2, 1fr)',
					gap: '8px',
					height: '168px',
				}}
			>
				{galleryImages.slice(0, 2).map((image, idx) => (
					<img
						key={`image-0-${idx}`}
						src={image.imgUrl}
						alt={`Gallery Image 0-${idx}`}
						style={{ width: '100%', height: '168px', objectFit: 'cover' }}
					/>
				))}
			</div>
		);

		// Subsequent rows: 3 images each
		const remainder = galleryImages.slice(2);
		for (let i = 0; i < remainder.length; i += 3) {
			rows.push(
				<div
					key={`row-${Math.floor(i / 3) + 1}`}
					style={{
						display: 'grid',
						gridTemplateColumns: 'repeat(3, 1fr)',
						gap: '8px',
						height: '110px',
					}}
				>
					{remainder.slice(i, i + 3).map((image, idx) => (
						<img
							key={`image-${i + idx + 2}`}
							src={image.imgUrl}
							alt={`Gallery Image ${i + idx + 2}`}
							style={{ width: '100%', height: '110px', objectFit: 'cover' }}
						/>
					))}
				</div>
			);
		}

		return rows;
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Gallery Settings', 'custom-gallery')} initialOpen={true}>
					<TextControl
						label={__('Padding Top (px)', 'custom-gallery')}
						type="number"
						value={paddingTop}
						onChange={(val) => setAttributes({ paddingTop: parseInt(val, 10) || 0 })}
					/>
					<TextControl
						label={__('Padding Bottom (px)', 'custom-gallery')}
						type="number"
						value={paddingBottom}
						onChange={(val) => setAttributes({ paddingBottom: parseInt(val, 10) || 0 })}
					/>
					<TextControl
						label={__('Padding Left (px)', 'custom-gallery')}
						type="number"
						value={paddingLeft}
						onChange={(val) => setAttributes({ paddingLeft: parseInt(val, 10) || 0 })}
					/>
					<TextControl
						label={__('Padding Right (px)', 'custom-gallery')}
						type="number"
						value={paddingRight}
						onChange={(val) => setAttributes({ paddingRight: parseInt(val, 10) || 0 })}
					/>

					<MediaUploadCheck>
						<div style={{ display: 'flex', justifyContent: 'space-between' }}>
							{/* Add Images Button */}
							<MediaUpload
								onSelect={addToGallery}
								allowedTypes={['image']}
								multiple
								gallery
								render={({ open }) => (
									<Button
										icon={plus}
										onClick={open}
										isPrimary
										label={__('Add Images', 'custom-gallery')}
									/>
								)}
							/>

							{/* Edit Gallery Button */}
							<MediaUpload
								onSelect={handleUpdateGallery}
								allowedTypes={['image']}
								multiple
								gallery
								value={galleryImages.map((img) => img.imgId)}
								render={({ open }) => (
									<Button
										icon={edit}
										onClick={open}
										label={__('Edit Gallery', 'custom-gallery')}
										variant="secondary"
										aria-label={__('Edit Gallery', 'custom-gallery')}
										style={{
											width: '40px',
											height: '40px',
											display: 'flex',
											justifyContent: 'center',
											alignItems: 'center',
										}}
									/>
								)}
							/>
						</div>
					</MediaUploadCheck>

					{galleryImages.length > 0 && (
						<div style={{ marginTop: '1rem' }}>
							<DragDropContext onDragEnd={onDragEnd}>
								<Droppable droppableId="galleryImagesDroppable" direction="horizontal">
									{(provided) => (
										<div
											ref={provided.innerRef}
											{...provided.droppableProps}
											style={{ display: 'flex', flexWrap: 'wrap', gap: '8px' }}
										>
											{galleryImages.map((image, index) => (
												<Draggable
													key={image.imgId || index}
													draggableId={`${image.imgId || index}`}
													index={index}
												>
													{(prov) => (
														<div
															ref={prov.innerRef}
															{...prov.draggableProps}
															{...prov.dragHandleProps}
															style={{
																position: 'relative',
																userSelect: 'none',
																...prov.draggableProps.style,
															}}
														>
															<img
																src={image.imgUrl}
																alt={__('Gallery Image', 'custom-gallery')}
																style={{
																	width: '45px',
																	height: '45px',
																	objectFit: 'cover',
																	display: 'block',
																	borderRadius: '4px',
																}}
															/>
															<Button
																isSmall
																isDestructive
																onClick={() => deleteImage(index)}
																aria-label={__('Delete image', 'custom-gallery')}
																style={{
																	position: 'absolute',
																	top: '-10px',
																	right: '-10px',
																	width: '28px',
																	height: '28px',
																	padding: 0,
																	backgroundColor: '#d93025',
																	borderRadius: '50%',
																	border: '2px solid white',
																	boxShadow: '0 2px 8px rgba(0,0,0,0.3)',
																	display: 'flex',
																	justifyContent: 'center',
																	alignItems: 'center',
																	cursor: 'pointer',
																	transition: 'transform 0.2s ease, background-color 0.2s ease',
																	color: '#fff'
																}}
																onMouseEnter={e => {
																	e.currentTarget.style.backgroundColor = '#a52714';
																	e.currentTarget.style.transform = 'scale(1.1)';
																}}
																onMouseLeave={e => {
																	e.currentTarget.style.backgroundColor = '#d93025';
																	e.currentTarget.style.transform = 'scale(1)';
																}}
															>
																{trash}
															</Button>
														</div>
													)}
												</Draggable>
											))}
											{provided.placeholder}
										</div>
									)}
								</Droppable>
							</DragDropContext>
						</div>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()} style={galleryStyle}>
				{galleryImages.length > 0 ? (
					renderRows()
				) : (
					<p>{__('No images selected.', 'custom-gallery')}</p>
				)}
			</div>
		</>
	);
};

export default GalleryEdit;
