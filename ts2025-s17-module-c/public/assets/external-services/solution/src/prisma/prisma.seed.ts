import { hash } from 'argon2';
import { HistoryType, PrismaClient, TariffType } from '../../generated/prisma';

const prisma = new PrismaClient();

async function main() {
	const user1 = await prisma.user.upsert({
		where: {
			email: 'user1@gmail.com'
		},
		update: {},
		create: {
			name: 'User 1',
			email: 'user1@gmail.com',
			phone: '+77777777771',
			password: await hash('ts2025u1'),
			balance: 10000
		},
	});

	const user2 = await prisma.user.upsert({
		where: {
			email: 'user2@gmail.com'
		},
		update: {},
		create: {
			name: 'User 2',
			email: 'user2@gmail.com',
			phone: '+77777777772',
			password: await hash('ts2025u2'),
			balance: 10000
		},
	});

	const user3 = await prisma.user.upsert({
		where: {
			email: 'user3@gmail.com'
		},
		update: {},
		create: {
			name: 'User 3',
			email: 'user3@gmail.com',
			phone: '+77777777773',
			password: await hash('ts2025u3'),
			balance: 10000
		},
	});

	const user4 = await prisma.user.upsert({
		where: {
			email: 'user4@gmail.com'
		},
		update: {},
		create: {
			name: 'User 4',
			email: 'user4@gmail.com',
			phone: '+77777777774',
			password: await hash('ts2025u4'),
			balance: 13845
		},
	});

	const balanceHistory1 = await prisma.balanceHistory.create({
		data: {
			type: HistoryType.REPLENISHMENT,
			value: 5000,
			userId: user4.id
		}
	});

	const balanceHistory2 = await prisma.balanceHistory.create({
		data: {
			type: HistoryType.REPLENISHMENT,
			value: 2000,
			userId: user4.id
		}
	});

	const balanceHistory3 = await prisma.balanceHistory.create({
		data: {
			type: HistoryType.WITHDRAWAL,
			value: 1235,
			userId: user4.id
		}
	});

	const balanceHistory5 = await prisma.balanceHistory.create({
		data: {
			type: HistoryType.RENTAL,
			value: 3900,
			userId: user4.id
		}
	});

	const balanceHistory6 = await prisma.balanceHistory.create({
		data: {
			type: HistoryType.MODERATION_REWARD,
			value: 1980,
			userId: user4.id
		}
	});

	console.log('LOG [seed]: users created');

	const category1 = await prisma.category.create({
		data: {
			name: 'City Bicycles',
			userId: user1.id
		},
	});

	const category2 = await prisma.category.create({
		data: {
			name: 'Mountain Bicycles',
			userId: user1.id
		},
	});

	const category3 = await prisma.category.create({
		data: {
			name: 'City Bicycles',
			userId: user2.id
		},
	});

	console.log('LOG [seed]: categories created');

	const tariff1 = await prisma.tariff.create({
		data: {
			name: 'Standard',
			type: TariffType.STATIC,
			basePrice: 100,
			categoryId: category1.id,
		}
	});

	const tariff2 = await prisma.tariff.create({
		data: {
			name: 'Economy',
			type: TariffType.DYNAMIC,
			basePrice: 75,
			maxPrice: 150,
			minPrice: 25,
			categoryId: category1.id,
		}
	});

	console.log('LOG [seed]: tariffs created');

	const bicycle1 = await prisma.bicycle.create({
		data: {
			name: 'MountainX',
			slug: 'mountain-x',
			description: 'Durable mountain bike with suspension and wide tires, designed for riding on rough terrain.',
			locationX: '3197.13',
			locationY: '125.05',
			categoryId: category1.id,
			pathToImage: 'bicycle-1.jpg'
		}
	});

	const bicycle2 = await prisma.bicycle.create({
		data: {
			name: 'Roadster',
			slug: 'roadster',
			description: 'Lightweight and fast bike for comfortable rides around the city and on smooth roads.',
			locationX: '1375.15',
			locationY: '1116.05',
			categoryId: category1.id,
			pathToImage: 'bicycle-2.jpg'
		}
	});

	const bicycle3 = await prisma.bicycle.create({
		data: {
			name: 'SwiftRide',
			slug: 'swift-ride',
			description: 'Agile city bike with an upright seating position, ideal for everyday short-distance trips.',
			locationX: '132.68',
			locationY: '994.19',
			categoryId: category1.id,
			pathToImage: 'bicycle-3.jpg'
		}
	});

	console.log('LOG [seed]: bicycles created');

	const history1 = await prisma.booking.create({
		data: {
			rating: 4,
			pricePerMin: 20,
			fullPrice: 20 * 5,
			percentageOfWear: 5,
			startedAt: new Date(),
			endedAt: new Date(Date.now() + 60 * 1000 * 5),
			bicycleId: bicycle1.id,
			tariffId: tariff1.id,
			userId: user3.id,
			photos: JSON.stringify(["bicycle-1.jpg", "bicycle-2.jpg"])
		}
	});

	const history2 = await prisma.booking.create({
		data: {
			rating: 5,
			pricePerMin: 70,
			fullPrice: 70 * 14,
			percentageOfWear: 8,
			startedAt: new Date(),
			endedAt: new Date(Date.now() + 60 * 1000 * 14),
			bicycleId: bicycle2.id,
			tariffId: tariff1.id,
			userId: user3.id,
			photos: JSON.stringify(["bicycle-1.jpg", "bicycle-2.jpg"])
		}
	});

	const history3 = await prisma.booking.create({
		data: {
			rating: 5,
			pricePerMin: 50,
			fullPrice: 50 * 8,
			percentageOfWear: 4,
			startedAt: new Date(),
			endedAt: new Date(Date.now() + 60 * 1000 * 8),
			bicycleId: bicycle2.id,
			tariffId: tariff2.id,
			userId: user3.id,
			photos: JSON.stringify(["bicycle-1.jpg", "bicycle-2.jpg"])
		}
	});

	const history4 = await prisma.booking.create({
		data: {
			rating: 4,
			pricePerMin: 100,
			fullPrice: 100 * 30,
			percentageOfWear: 14,
			startedAt: new Date(),
			endedAt: new Date(Date.now() + 60 * 1000 * 30),
			bicycleId: bicycle2.id,
			tariffId: tariff2.id,
			userId: user3.id,
			photos: JSON.stringify(["bicycle-1.jpg", "bicycle-2.jpg"])
		}
	});

	const history5 = await prisma.booking.create({
		data: {
			pricePerMin: 30,
			fullPrice: 60,
			percentageOfWear: 2,
			startedAt: new Date(),
			endedAt: new Date(Date.now() + 60 * 1000 * 2),
			bicycleId: bicycle3.id,
			tariffId: tariff1.id,
			userId: user4.id,
			photos: JSON.stringify(["bicycle-1.jpg", "bicycle-2.jpg"])
		}
	});

	console.log('LOG [seed]: histories created');

	const application1 = await prisma.application.create({
		data: {
			categoryId: category1.id,
			userId: user3.id
		}
	});

	const application2 = await prisma.application.create({
		data: {
			categoryId: category1.id,
			userId: user4.id
		}
	});

	console.log('LOG [seed]: applications created');
}

main();