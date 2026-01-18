import chromium from '@sparticuz/chromium';

export default {
    async fetch(request: Request) {
        const path = await chromium.executablePath();
        console.log(`${path}`);
        return new Response(`${path}`);
    },
};
