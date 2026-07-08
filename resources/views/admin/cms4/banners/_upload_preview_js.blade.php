<script>
    function bannerCssUrl(url) {
        if (!url) {
            return '';
        }

        try {
            const parsed = new URL(url, window.location.origin);
            parsed.pathname = parsed.pathname
                .split('/')
                .map(function (part) {
                    return encodeURIComponent(decodeURIComponent(part));
                })
                .join('/');
            return parsed.toString();
        } catch (error) {
            return encodeURI(url);
        }
    }
</script>
