const secrets = require('../../secrets.json');

exports.loginAsAdmin = async function (page) {
    await page.goto(secrets.adminUrl);
    await page.fill('input[name="login_name"]', secrets.admin.username);
    await page.fill('input[name="login_psw"]', secrets.admin.password);
    await page.click('input[type="submit"]');
    await page.waitForURL(/.*admin/);
};

exports.loginAsUser = async function (page) {
    await page.goto(secrets.frontendUrl);
    await page.fill('input[name="login_name"]', secrets.user.username);
    await page.fill('input[name="login_psw"]', secrets.user.password);

    // Submit the form
    await Promise.all([
        page.click('input[type="submit"][name="login"]')      // precise selector
    ]);

};