const { randomUUID } = require('node:crypto');
const { test, expect } = require('@playwright/test');

const baseUrl = 'http://127.0.0.1:8000';
const png = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+X2ZkAAAAASUVORK5CYII=',
    'base64',
);

test('a user can publish and delete an image without reloading', async ({ page }) => {
    const email = process.env.E2E_EMAIL;
    const password = process.env.E2E_PASSWORD;

    if (!email || !password) {
        throw new Error('Set E2E_EMAIL and E2E_PASSWORD before running this test.');
    }

    await page.goto(`${baseUrl}/login`);
    const loginForm = page.locator('form').filter({
        has: page.locator('input[name="password"]'),
    });

    await loginForm.locator('input[name="email"]').fill(email);
    await loginForm.locator('input[name="password"]').fill(password);
    await loginForm.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/dashboard\/?$/);

    // A full page reload would clear this value.
    const pageMarker = randomUUID();
    await page.evaluate((marker) => {
        window.dashboardTestMarker = marker;
    }, pageMarker);

    const title = `Browser test ${randomUUID()}`;
    const publishForm = page.locator('#publish-image');

    await publishForm.getByLabel('Title').fill(title);
    await publishForm.getByLabel('Description').fill('Temporary acceptance test image.');
    await publishForm.getByLabel('Filter').selectOption('none');
    await publishForm.locator('input[type="file"]').setInputFiles({
        name: 'test-image.png',
        mimeType: 'image/png',
        buffer: png,
    });

    await publishForm.getByRole('button', { name: 'Publish', exact: true }).click();

    const card = page.locator('#my-images article').filter({
        has: page.getByRole('heading', { name: title, exact: true }),
    });

    await expect(card).toBeVisible();
    await expect(card.getByRole('img', { name: title, exact: true })).toHaveJSProperty(
        'complete',
        true,
    );
    await expect.poll(() => card.locator('img').evaluate((image) => image.naturalWidth))
        .toBeGreaterThan(0);
    expect(await page.evaluate(() => window.dashboardTestMarker)).toBe(pageMarker);

    const deleteButton = card.getByRole('button', { name: 'Delete', exact: true });
    // Accept the confirmation dialog shown by the Delete button.
    page.once('dialog', (dialog) => dialog.accept());
    await deleteButton.click();

    await expect(card).toHaveCount(0);
    expect(await page.evaluate(() => window.dashboardTestMarker)).toBe(pageMarker);

    // Reload deliberately to confirm the deletion persisted.
    await page.reload();
    await expect(page.locator('#my-images')).toBeVisible();
    await expect(card).toHaveCount(0);
});