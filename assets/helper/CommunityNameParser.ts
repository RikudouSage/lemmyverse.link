export class CommunityNameParser {
    public isValid(community: string): boolean {
        const regex = /^(?<CommunityName>[a-z0-9_]+)@(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])$/;
        return regex.test(community);
    }
}
