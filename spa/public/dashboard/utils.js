/**
 * Converts a regex route pattern to a route parameter format.
 *
 * @param {string} regexPattern - The regex pattern to convert (e.g., '/^orders\/(\d+)$/').
 * @return {string} The converted route with parameter placeholders (e.g., 'orders/:id').
 */
export function convertRegexToRouteParam( regexPattern ) {
    // Remove leading '^' and trailing '$' anchors
    let route = String(regexPattern).replace( /^\/\^/, '' ).replace( /\$\/$/, '' );

    // Replace escaped forward slashes with regular slashes
    route = route.replace( /\\\//g, '/' );

    // Replace regex capturing groups with route parameters
    // (\d+) becomes :id, (\w+) becomes :slug, etc.
    route = route.replace( /\(\\d\+\)/g, ':id' );
    route = route.replace( /\(\\w\+\)/g, ':slug' );
    route = route.replace( /\(\.\*\)/g, ':any' );
    route = route.replace( /\(\[a-zA-Z0-9_-\]\+\)/g, ':param' );

    return route;
}

// Usage example
// const regexPattern = '/^orders\/(\d+)$/';
// const routeParam = convertRegexToRouteParam( regexPattern );
// console.log( routeParam ); // Output: 'orders/:id'
