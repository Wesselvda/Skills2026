const { ExportLimiter } = require("./ExportLimiter");

describe("ExportLimiter", () => {
    test("Checks if the exportlimiter returns the correct values", () => {
        let exportLimiter = new ExportLimiter(20);

        expect(exportLimiter.distribute([1, 2, 3])).toStrictEqual({
            0: 1,
            1: 2,
            2: 3,
        });
        expect(exportLimiter.distribute([1, 23, 3])).toStrictEqual({
            0: 0.741,
            1: 17.037,
            2: 2.222,
        });
        expect(exportLimiter.distribute([3.3334, 6.6666, 10])).toStrictEqual({
            0: 3.333,
            1: 6.667,
            2: 10,
        });
    });
});
