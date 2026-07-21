import { useBlockProps } from '@wordpress/block-editor';

const GallerySave = ({ attributes }) => {
	const {
		galleryImages = [],
		paddingTop = 0,
		paddingBottom = 0,
		paddingLeft = 0,
		paddingRight = 0,
		backgroundColor = '#ffffff',
	} = attributes;

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

	const renderRows = () => {
		if (!galleryImages.length) {
			return null;
		}

		const rows = [];
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
		<div {...useBlockProps.save({ style: galleryStyle })}>
			{galleryImages.length > 0 ? (
				renderRows()
			) : (
				<p>No images selected.</p>
			)}
		</div>
	);
};

export default GallerySave;
