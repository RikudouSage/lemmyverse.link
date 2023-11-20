import {Controller} from "@hotwired/stimulus";
import {sprintf} from "sprintf-js";

export default class extends Controller {
    private readonly regexes = {
        community: /^https:\/\/(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])\/c\/(?<Community>[a-zA-Z0-9_]+)$/,
        user: /^https:\/\/(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])\/u\/(?<Username>[a-zA-Z0-9_]+)$/,
        post: /^https:\/\/(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])\/post\/(?<PostId>[0-9_]+)$/,
        // comment: /^https:\/\/(?<Instance>[a-zA-Z0-9][a-zA-Z0-9-.]{0,61}[a-zA-Z0-9])\/comment\/(?<CommentId>[0-9_]+)$/,
    }

    static override targets = [
        'linkInput',
        'linkPlaceholder',
        'copyToClipboardResult',
        'error',
        'result',
    ];
    static override values = {
        linkTemplateCommunity: String,
        linkTemplateUser: String,
        linkTemplatePost: String,
    };

    private linkInputTarget: HTMLInputElement;
    private linkPlaceholderTarget: HTMLSpanElement;
    private copyToClipboardResultTarget: HTMLDivElement;
    private errorTarget: HTMLParagraphElement;
    private resultTarget: HTMLDivElement;

    private linkTemplateCommunityValue: string;
    private linkTemplateUserValue: string;
    private linkTemplatePostValue: string;

    public createLink(): void {
        this.errorTarget.classList.add('hidden');
        this.resultTarget.classList.add('hidden');
        this.copyToClipboardResultTarget.classList.add('hidden');

        const link = this.linkInputTarget.value;

        let target: string | null = null;
        if (this.regexes.community.test(link)) {
            const matches = link.match(this.regexes.community);
            target = sprintf(this.linkTemplateCommunityValue, `${matches.groups!['Community']}@${matches.groups!['Instance']}`);
        } else if (this.regexes.user.test(link)) {
            const matches = link.match(this.regexes.user);
            target = sprintf(this.linkTemplateUserValue, `${matches.groups!['Username']}@${matches.groups!['Instance']}`);
        } else if (this.regexes.post.test(link)) {
            const matches = link.match(this.regexes.post);
            target = sprintf(this.linkTemplatePostValue, matches.groups!['Instance'], matches.groups!['PostId']);
        } else {
            this.errorTarget.classList.remove('hidden');
            return;
        }

        this.linkPlaceholderTarget.innerHTML = `<a href="${target}">${target}</a>`;
        this.linkPlaceholderTarget.dataset.link = target;
        this.resultTarget.classList.remove('hidden');
    }

    public async copyToClipboard(): Promise<void> {
        this.copyToClipboardResultTarget.classList.add('hidden');
        await navigator.clipboard.writeText(this.linkPlaceholderTarget.dataset.link);
        this.copyToClipboardResultTarget.classList.remove('hidden');
    }
}
