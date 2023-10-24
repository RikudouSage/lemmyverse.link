import {Controller} from "@hotwired/stimulus";
import {NameParser} from "../helper/NameParser";
import {sprintf} from "sprintf-js";

export default class extends Controller {
    static override targets = [
        'communityOrUser',
        'linkPlaceholder',
        'copyToClipboardResult',
        'error',
        'result',
    ];
    static override values = {
        linkTemplateCommunity: String,
        linkTemplateUser: String,
    };

    private nameParser: NameParser = new NameParser();

    private communityOrUserTarget: HTMLInputElement;
    private linkPlaceholderTarget: HTMLSpanElement;
    private copyToClipboardResultTarget: HTMLDivElement;
    private errorTarget: HTMLParagraphElement;
    private resultTarget: HTMLDivElement;

    private linkTemplateCommunityValue: string;
    private linkTemplateUserValue: string;

    public createLink(): void {
        this.errorTarget.classList.add('hidden');
        this.resultTarget.classList.add('hidden');
        this.copyToClipboardResultTarget.classList.add('hidden');

        let communityOrUser = this.communityOrUserTarget.value;
        const isUser = communityOrUser.startsWith('@');
        if (communityOrUser.startsWith('!') || communityOrUser.startsWith('@')) {
            communityOrUser = communityOrUser.substring(1);
        }
        if (!this.nameParser.isValid(communityOrUser)) {
            this.errorTarget.classList.remove('hidden');
            return;
        }

        const link = isUser
            ? sprintf(this.linkTemplateUserValue, communityOrUser)
            : sprintf(this.linkTemplateCommunityValue, communityOrUser)
        ;

        this.linkPlaceholderTarget.innerHTML = `<a href="${link}">${link}</a>`;
        this.linkPlaceholderTarget.dataset.link = link;
        this.resultTarget.classList.remove('hidden');
    }

    public async copyToClipboard(): Promise<void> {
        this.copyToClipboardResultTarget.classList.add('hidden');
        await navigator.clipboard.writeText(this.linkPlaceholderTarget.dataset.link);
        this.copyToClipboardResultTarget.classList.remove('hidden');
    }
}
