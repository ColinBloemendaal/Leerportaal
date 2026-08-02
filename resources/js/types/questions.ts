export interface MultipleChoiceOption {
    id: string;
    text: string;
}

export interface MultipleChoicePayload {
    options: MultipleChoiceOption[];
    correct_option_id: string;
}

export interface MultipleResponseOption {
    id: string;
    text: string;
}

export interface MultipleResponsePayload {
    options: MultipleResponseOption[];
    correct_option_ids: string[];
}

export interface TrueFalsePayload {
    correct_answer: boolean;
}

export type OpenShortMatchMode = 'exact' | 'contains' | 'regex';

export interface OpenShortPayload {
    match_mode: OpenShortMatchMode;
    case_sensitive: boolean;
    acceptable_answers: string[];
}

export interface EssayRubricCriterion {
    criterion: string;
    points: number;
}

export interface EssayPayload {
    rubric: EssayRubricCriterion[];
    min_words: number | null;
    max_words: number | null;
}

export interface MatchingPair {
    id: string;
    left: string;
    right: string;
}

export interface MatchingPayload {
    pairs: MatchingPair[];
}

export interface OrderingItem {
    id: string;
    text: string;
}

export interface OrderingPayload {
    items: OrderingItem[];
}

export interface FillInBlankBlank {
    id: string;
    acceptable_answers: string[];
    case_sensitive: boolean;
}

export interface FillInBlankPayload {
    template: string;
    blanks: FillInBlankBlank[];
}

export interface DropdownInTextBlank {
    id: string;
    options: string[];
    correct_option: string;
}

export interface DropdownInTextPayload {
    template: string;
    blanks: DropdownInTextBlank[];
}

export interface NumericPayload {
    correct_answer: number;
    tolerance: number;
}

export interface HotspotRegion {
    id: string;
    x: number;
    y: number;
    width: number;
    height: number;
    label: string;
}

export interface HotspotImagePayload {
    image_url: string;
    regions: HotspotRegion[];
    correct_region_ids: string[];
}
