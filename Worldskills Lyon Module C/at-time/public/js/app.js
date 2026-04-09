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

