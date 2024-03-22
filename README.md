# Cashfree Payment Gateway Extension

This extension enables users to seamlessly integrate the Cashfree Payment Gateway into their Paymenter tailored for Indian hostings. With this extension, clients can securely make payments via a wide range of options, including any card, 65+ netbanking options, UPI, GPay, PhonePe & other wallets, EMI, and Pay Later options. This ensures a smooth and hassle-free payment experience specifically designed for Indian Hosting Services.

## Configuration

1. **Whitelist Your Domain:** Start by whitelisting your domain in the Cashfree Dashboard. It should match your Paymenter domain, for example, `billing.stellarhost.tech`.
2. **Configure Webhooks:** Add Webhooks with events "Success Payments" and "Failed Payments". Ensure the Webhook URL format is `https://<your_paymenter_url>/extensions/cashfree/webhook`. For example, if your Paymenter URL is `billing.stellarhost.tech`, the webhook URL should be `https://billing.stellarhost.tech/extensions/cashfree/webhook`. Make sure to include `https://` and `/extensions/cashfree/webhook`.
3. **Enable Cashfree Extension:** Navigate to Paymenter's Extensions Settings, enable the Cashfree extension, and provide your API Key details.

Congratulations! Your Cashfree Payment Gateway setup is now complete!

Note: It might take time to get your domain whitelisted/recieve webhook from Cashfree so you just have to wait ;-;

## Support

For any assistance or queries, please reach out to [@sarthak77](https://discord.stellarhost.tech/) on Discord.

Extension Version: v1.0.0

Cashfree API Version: v4 2023-08-01 [Latest]
