import React from 'react';

import GlobalStyles from '../../base/ui/components/GlobalStyles.web';
import JitsiThemeProvider from '../../base/ui/components/JitsiThemeProvider.web';
import DialogContainer from '../../base/ui/components/web/DialogContainer';
import ChromeExtensionBanner from '../../chrome-extension-banner/components/ChromeExtensionBanner.web';
import OverlayContainer from '../../overlay/components/web/OverlayContainer';

import {AbstractApp} from './AbstractApp';

// Register middlewares and reducers.
import '../middlewares';
import '../reducers';
import {QueryClient, QueryClientProvider} from "@tanstack/react-query";

const queryClient = new QueryClient();

/**
 * Root app {@code Component} on Web/React.
 *
 * @augments AbstractApp
 */
export class App extends AbstractApp {

    /**
     * Creates an extra {@link ReactElement}s to be added (unconditionally)
     * alongside the main element.
     *
     * @abstract
     * @protected
     * @returns {ReactElement}
     */
    _createExtraElement() {
        return (
            <QueryClientProvider client={queryClient}>
                <JitsiThemeProvider>
                    <OverlayContainer/>
                </JitsiThemeProvider>
            </QueryClientProvider>
        );
    }

    /**
     * Overrides the parent method to inject {@link AtlasKitThemeProvider} as
     * the top most component.
     *
     * @override
     */
    _createMainElement(component: React.ComponentType, props?: Object) {
        return (
            <QueryClientProvider client={queryClient}>
                <JitsiThemeProvider>
                    <GlobalStyles/>
                    {super._createMainElement(component, props)}
                </JitsiThemeProvider>
            </QueryClientProvider>
        );
    }

    /**
     * Renders the platform specific dialog container.
     *
     * @returns {React$Element}
     */
    _renderDialogContainer() {
        return (
            <QueryClientProvider client={queryClient}>
                <JitsiThemeProvider>
                    <DialogContainer/>
                </JitsiThemeProvider>
            </QueryClientProvider>
        );
    }
}
