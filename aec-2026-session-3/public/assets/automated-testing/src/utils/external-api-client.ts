import { FetchClient } from "./fetch-client";

export interface DiscountRule {
  id: string;
  name: string;
  conditions: {
    minQuantity: number | null;
    minTotalPrice: number | null;
    timespan: { start: string; end: string } | null;
  };
  discount: {
    type: "fixed" | "percentage";
    value: number;
  };
}

export interface HealthStatus {
  status: string;
}

export interface Store {
  storeId: number;
  name: string;
  address: string;
  city: string;
}

export interface BookAvailability {
  storeId: number;
  bookId: number;
  availability: "high" | "low" | "out_of_stock";
  inventory: number | string | null;
}

export interface ModerationResult {
  flaggedWords: Array<{
    start: number;
    category: string;
  }>;
}

export interface PaymentSessionResponse {
  sessionId: string;
  paymentUrl?: string;
  status: "pending" | "successful" | "failed";
  expiresAt?: string;
  amount?: number;
  currency?: string;
  paidAt?: string;
  errorCode?: string;
}

export interface TranslationResponse {
  translation: string;
}

export interface BatchTranslationResult {
  success: boolean;
  translation?: string;
  message?: string;
}

export class ExternalApiClient extends FetchClient {
  async getDiscountRules(): Promise<DiscountRule[]> {
    const { body } = await this.get("/api/discounts");
    return body;
  }

  async healthCheck(): Promise<HealthStatus> {
    const { body } = await this.get("/api/health");
    return body;
  }

  async getStoresForBook(bookId: string): Promise<Store[]> {
    const { body } = await this.get(`/api/inventory/stores/for-book/${bookId}`);
    return body;
  }

  async getBookAvailabilityAtStore(
    storeId: string,
    bookId: string,
  ): Promise<BookAvailability> {
    const { body } = await this.get(
      `/api/inventory/stores/${storeId}/books/${bookId}`,
    );
    return body;
  }

  async checkContent(text: string): Promise<ModerationResult> {
    const { body } = await this.post("/api/moderation/check", { text });
    return body;
  }

  async createPaymentSession(data: {
    amount: number;
    orderId: string;
    expiresAt: string;
    callbackUrl: string;
  }): Promise<PaymentSessionResponse> {
    const { body } = await this.post("/api/payment/session", data);
    return body;
  }

  async getPaymentSession(sessionId: string): Promise<PaymentSessionResponse> {
    const { body } = await this.get(`/api/payment/session/${sessionId}`);
    return body;
  }

  async cancelPaymentSession(
    sessionId: string,
  ): Promise<PaymentSessionResponse> {
    const { body } = await this.delete(`/api/payment/session/${sessionId}`, {});
    return body;
  }

  async translateText(data: {
    text: string;
    sourceLanguage: string;
    targetLanguage: string;
  }): Promise<TranslationResponse> {
    const { body } = await this.post("/api/translation/translate", data);
    return body;
  }

  async batchTranslateTexts(
    data: {
      texts: Array<{
        text: string;
        sourceLanguage: string;
        targetLanguage: string;
      }>;
    },
    doNotTranslate = false,
  ): Promise<BatchTranslationResult[]> {
    if (doNotTranslate) {
      return data.texts.map((text) => ({
        success: true,
        translation: text.text,
      }));
    }
    const { body } = await this.post("/api/translation/batch", data);
    return body;
  }
}
