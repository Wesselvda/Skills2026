const spotlight = document.querySelector('[data-cover-spotlight]');
if (spotlight) {
	const setSpotlightPosition = (event) => {
		const rect = spotlight.getBoundingClientRect();
		const x = event.clientX - rect.left;
		const y = event.clientY - rect.top;

		spotlight.style.setProperty('--spot-x', `${x}px`);
		spotlight.style.setProperty('--spot-y', `${y}px`);
		spotlight.style.setProperty('--spot-opacity', '1');
	};

	spotlight.addEventListener('mousemove', setSpotlightPosition);
	spotlight.addEventListener('mouseenter', setSpotlightPosition);
	spotlight.addEventListener('mouseleave', () => {
		spotlight.style.setProperty('--spot-opacity', '0');
	});
}

const lightbox = document.querySelector('[data-image-lightbox]');
const lightboxImage = lightbox?.querySelector('img');

if (lightbox && lightboxImage) {
	const closeLightbox = () => {
		lightbox.hidden = true;
		lightboxImage.src = '';
	};

	document.addEventListener('click', (event) => {
		const image = event.target.closest('.heritage-content img');
		if (image) {
			lightboxImage.src = image.src;
			lightbox.hidden = false;
			return;
		}

		if (!lightbox.hidden) {
			closeLightbox();
		}
	});

	window.addEventListener('scroll', () => {
		if (!lightbox.hidden) {
			closeLightbox();
		}
	}, { passive: true });
}
