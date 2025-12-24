# Environment Variables Setup

This project uses Vite for building, which has specific requirements for environment variables.

## How Vite Environment Variables Work

1. **Prefix Required**: Only environment variables prefixed with `VITE_` are exposed to client-side code
2. **Build-time Replacement**: Variables are replaced at build time, not runtime
3. **Automatic Loading**: Vite automatically loads `.env` files from the project root

## Setup Instructions

### 1. Create Environment Files

Create the following files in the `client/` directory:

#### `.env` (for local development)
```bash
VITE_API_BASE_URL=https://bookmarkhive.test
```

#### `.env.development` (loaded automatically when running `yarn dev`)
```bash
VITE_API_BASE_URL=https://bookmarkhive.test
```

#### `.env.production` (loaded automatically when running `yarn build`)
```bash
VITE_API_BASE_URL=https://api.bookmarkhive.com
```

### 2. Using Environment Variables in Code

Access variables via `import.meta.env`:

```typescript
const apiUrl = import.meta.env.VITE_API_BASE_URL;
```

### 3. Build Process

When you run `yarn build`, Vite will:
- Load variables from `.env.production` (or `.env` if `.env.production` doesn't exist)
- Replace all `import.meta.env.VITE_*` references in your code with the actual values
- Bundle these values directly into the built JavaScript files

### 4. Environment File Priority

Vite loads environment files in this order (later files override earlier ones):
1. `.env` - loaded in all cases
2. `.env.local` - loaded in all cases, ignored by git
3. `.env.[mode]` - only loaded in specified mode (e.g., `.env.production`)
4. `.env.[mode].local` - only loaded in specified mode, ignored by git

### 5. Important Notes

- **Security**: Never commit `.env.local` files or files containing secrets
- **Build-time**: Variables are replaced at build time, so you need to rebuild if you change them
- **Client-side**: All `VITE_*` variables are exposed to the browser - don't put secrets here
- **Type Safety**: You can add type definitions in `src/vite-env.d.ts` if needed

### Example: Adding a New Variable

1. Add to `.env`:
   ```bash
   VITE_APP_NAME=BookmarkHive
   ```

2. Use in code:
   ```typescript
   const appName = import.meta.env.VITE_APP_NAME;
   ```

3. Rebuild:
   ```bash
   yarn build
   ```

