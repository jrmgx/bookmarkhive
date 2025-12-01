# BookmarkHive web extension

The decentralized social bookmarking service official web extension.

## Development

### Requirements

You will need: 
- Node.js (v16 or higher)
- npm or yarn

This project uses TypeScript, all source files are in the `src/` directory:
- Edit `.ts` files in `src/`
- Run `npm run build` to compile to `.js` files
- The compiled `.js` files are what the browser extension uses

### Installation

Installing dependencies:
```bash
npm install
```

Building the extension:
```bash
npm run build
```

Loading the extension in your browser:
   - **Chrome**: Go to `chrome://extensions/`, enable "Developer mode", click "Load unpacked", and select this directory
   - **Firefox**: Go to `about:debugging`, click "This Firefox", click "Load Temporary Add-on", and select `manifest.json`

### Work on new feature or bug fixes

- **Build once**: `npm run build`
- **Watch mode** (auto-rebuild on changes): `npm run watch`
- **Clean compiled files**: `npm run clean`
