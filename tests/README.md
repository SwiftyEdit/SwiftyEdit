# SwiftyEdit E2E Tests

This folder contains all end-to-end (E2E) tests for the SwiftyEdit CMS using [Playwright](https://playwright.dev/).

## 📦 Installation

Install all required dependencies using:

```bash
pnpm install
```

Playwright is already listed as a development dependency in the `package.json`.

## ▶️ Running the tests

To run all tests:

```bash
pnpm test
```

To run tests in UI mode (useful for debugging):

```bash
pnpm exec playwright test --ui
```

Or to run a specific file:

```bash
pnpm exec playwright test tests/e2e/frontend/login.spec.js
```

## 🔐 secrets.json (required)

You must create a file named `secrets.json` in the root directory.

This file **must not be committed to Git** – it should be listed in your `.gitignore`.

### Example structure:

```json
{
  "adminUrl": "http://localhost/admin/",
  "frontendUrl": "http://localhost/",
  "admin": {
    "username": "admin@example.com",
    "password": "your_admin_password"
  },
  "user": {
    "username": "user@example.com",
    "password": "your_user_password"
  }
}
```

This file is used to log in during tests.

## 📁 Folder structure

```
tests/
├── e2e/
│   ├── admin/           # Admin/backend-related tests
│   └── frontend/        # Frontend/user-related tests
├── helpers/             # Shared functions (e.g. login scripts)
│   └── login.js
├── secrets.json         # Not versioned – must be created manually
└── README.md            # This file
```

## ✅ Login expectations

The test suite currently checks for successful login via:

- **Frontend user login**: Looks for an element `a.link-profile` (visible after login)
- **Admin login**: Can be adapted to assert presence of admin dashboard elements

## 🛠 Suggestions

- Add `data-testid` attributes to your HTML where possible for more stable selectors.
- Consider using test tags and grouping with `@playwright/test`'s `describe()` and `tag` features.
