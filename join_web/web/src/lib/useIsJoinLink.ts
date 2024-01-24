const useIsJoinLink = (): boolean => {

	return /join\/[a-zA-Z0-9]+\/?/.test(window.location.pathname)
};

export default useIsJoinLink;
