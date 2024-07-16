import { createBrowserRouter, RouterProvider } from "react-router-dom";
import ErrorPage from "./error-page.tsx";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import GlobalStartProvider from "./providers/GlobalStartProvider.tsx";
import { Provider } from "jotai";
import PageLayout from "./pages/PageLayout.tsx";
import { Page } from "./pages/Page.tsx";
import PageRequestMediaPermissions from "./pages/PageRequestMediaPermissions.tsx";

const router = createBrowserRouter([
	{
		path: "/",
		element: (
			<PageLayout>
				<Page />
			</PageLayout>
		),
		errorElement: <ErrorPage />,
		children: [
			{
				path: "talk/:param",
				element: (
					<PageLayout>
						<Page />
					</PageLayout>
				),
			},
			{
				path: "c/:param",
				element: (
					<PageLayout>
						<Page />
					</PageLayout>
				),
			},
			{
				path: "-/:param",
				element: (
					<PageLayout>
						<Page />
					</PageLayout>
				),
			},
		],
	},
	{
		path: "requestMediaPermissions",
		element: (
			<PageLayout>
				<PageRequestMediaPermissions />
			</PageLayout>
		),
		errorElement: <ErrorPage />,
	},
]);

const queryClient = new QueryClient();

export default function App() {
	return (
		<Provider>
			<QueryClientProvider client={queryClient}>
				<GlobalStartProvider>
					<RouterProvider router={router} />
				</GlobalStartProvider>
			</QueryClientProvider>
		</Provider>
	);
}
