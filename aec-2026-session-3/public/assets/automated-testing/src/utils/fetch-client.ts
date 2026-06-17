const TIMEOUT_MS = parseInt(process.env.REQUEST_TIMEOUT_MS ?? "15000");

export interface FetchResult {
  status: number;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  body: any;
}

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    public readonly body: any,
  ) {
    super(`API Error: ${status} - ${JSON.stringify(body)}`);
  }
}

export class FetchClient {
  constructor(private readonly _baseUrl: string) {}

  get baseUrl() {
    return this._baseUrl;
  }

  get(endpoint: string, acceptLanguage?: string) {
    return this.request(endpoint, {
      headers:
        acceptLanguage !== undefined
          ? {
              "Accept-Language": acceptLanguage,
              Accept: "application/json",
            }
          : {
              Accept: "application/json",
            },
    });
  }

  post(endpoint: string, body: unknown) {
    return this.request(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    });
  }

  put(endpoint: string, body: unknown) {
    return this.request(endpoint, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    });
  }

  patch(endpoint: string, body: unknown) {
    return this.request(endpoint, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    });
  }

  delete(endpoint: string, body: unknown) {
    return this.request(endpoint, {
      method: "DELETE",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(body),
    });
  }

  private async request(
    endpoint: string,
    options?: RequestInit,
  ): Promise<FetchResult> {
    const { response, body } = await this.fetch(
      `${this._baseUrl}${endpoint}`,
      options,
    );
    if (!response.ok) throw new ApiError(response.status, body);
    return { status: response.status, body };
  }

  async fetch(input: string | URL | Request, init?: RequestInit | undefined) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), TIMEOUT_MS);

    try {
      const response = await fetch(input, {
        ...init,
        signal: controller.signal,
      });

      let body: unknown;
      try {
        body = await response.json();
      } catch {
        body = null;
      }

      return { response, body };
    } finally {
      clearTimeout(timeout);
    }
  }
}
