export async function onRequest(context) {
    // First, try to serve the static asset
    const response = await context.next();

    // If the asset is found (e.g. main.js, logo.png), return it
    if (response.status !== 404) {
        return response;
    }

    // If not found (404), it might be a Client-Side Route (e.g. /chat)
    // Check if it's an API call (optional safeguard)
    const url = new URL(context.request.url);
    if (url.pathname.startsWith('/api')) {
        return response; // Let API 404s remain 404s
    }

    // Serve index.html with 200 OK
    return context.env.ASSETS.fetch(new URL('/index.html', context.request.url));
}
