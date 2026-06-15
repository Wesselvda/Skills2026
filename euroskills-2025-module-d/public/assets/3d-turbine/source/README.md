# 3D Turbine - Source Code

This directory contains the source code for the 3D Turbine visualization library.

**Note:** The browser-ready library (`3d-turbine.js`) is already included in `/assets/3d-turbine`, so you don't need to build it yourself.

## Development

For local development, a demo HTML page is provided that renders two turbines with different settings.

```bash
npm install
npm run dev
```

Then open http://localhost:5173/ to access the demo page.

## Building

If you need to rebuild the library:

```bash
npm install
npm run build
```

This will create the browser-ready `dist/3d-turbine.js` file.
