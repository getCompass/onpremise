import MiddlewareRegistry from '../base/redux/MiddlewareRegistry';
import './subscriber.native';

/**
 * The middleware of the feature Filmstrip.
 */
MiddlewareRegistry.register(store => next => action => {
    return next(action);
});
