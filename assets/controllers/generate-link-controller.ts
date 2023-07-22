import {Controller} from "@hotwired/stimulus";
import {CommunityNameParser} from "../helper/CommunityNameParser";
import {sprintf} from "sprintf-js";

export default class extends Controller {
    static override targets = [
        'community',
        'linkPlaceholder',
        'copyToClipboardResult',
        'error',
        'result',
    ];
    static override values = {
        linkTemplate: String,
    };

    private communityNameParser: CommunityNameParser = new CommunityNameParser();

    private communityTarget: HTMLInputElement;
    private linkPlaceholderTarget: HTMLSpanElement;
    private copyToClipboardResultTarget: HTMLDivElement;
    private errorTarget: HTMLParagraphElement;
    private resultTarget: HTMLDivElement;

    private linkTemplateValue: string;

    public createLink(): void {
        this.errorTarget.classList.add('hidden');
        this.resultTarget.classList.add('hidden');
        this.copyToClipboardResultTarget.classList.add('hidden');

        const community = this.communityTarget.value;
        if (!this.communityNameParser.isValid(community)) {
            this.errorTarget.classList.remove('hidden');
            return;
        }

        const link = sprintf(this.linkTemplateValue, community);

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
