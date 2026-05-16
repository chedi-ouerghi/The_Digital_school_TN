 1
db.restaurants.find();

 2
db.restaurant.find({}, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 3
db.restaurant.find({}, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1, _id: 0 });

 4
db.restaurant.find({}, { restaurant_id: 1, name: 1, borough: 1, zip: 1, _id: 0 });

 5
db.restaurant.find({ borough: "Bronx" });

 6
db.restaurant.find({ borough: "Bronx" }).limit(5);

 7
db.restaurant.find({ borough: "Bronx" }).skip(5).limit(5);

 8
db.restaurant.find({ "grades.score": { $gt: 90 } });

 9
db.restaurant.find({ "grades.score": { $gt: 80, $lt: 100 } });

 10
db.restaurant.find({ "address.coord.0": { $lt: -95.754168 } });

 11
db.restaurant.find({
  cuisine: { $ne: "American" },
  "grades.score": { $gt: 70 },
  "address.coord.0": { $lt: -65.754168 }
});

 12
db.restaurant.find({
  cuisine: { $ne: "American" },
  "grades.score": { $gt: 70 },
  "address.coord.1": { $lt: -65.754168 }
});

 13
db.restaurant.find({
  cuisine: { $ne: "American" },
  "grades.grade": "A",
  borough: { $ne: "Brooklyn" }
}).sort({ cuisine: -1 });

 14
db.restaurant.find({ name: /^Wil/ }, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 15
db.restaurant.find({ name: /ces$/ }, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 16
db.restaurant.find({ name: /Reg/ }, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 17
db.restaurant.find({
  borough: "Bronx",
  $or: [{ cuisine: "American" }, { cuisine: "Chinese" }]
});

 18
db.restaurant.find({
  borough: { $in: ["Staten Island", "Queens", "Bronx", "Brooklyn"] }
}, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 19
db.restaurant.find({
  borough: { $nin: ["Staten Island", "Queens", "Bronx", "Brooklyn"] }
}, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 20
db.restaurant.find({ "grades.score": { $not: { $gt: 10 } } });

 21
db.restaurant.find({
  $or: [
    { cuisine: { $nin: ["American", "Chinese"] } },
    { name: /^Wil/ }
  ]
}, { restaurant_id: 1, name: 1, borough: 1, cuisine: 1 });

 22
db.restaurant.find({
  "grades": { $elemMatch: { grade: "A", score: 11, date: ISODate("2014-08-11T00:00:00Z") } }
}, { restaurant_id: 1, name: 1, grades: 1 });

 23
db.restaurant.find({
  "grades.1": { grade: "A", score: 9, date: ISODate("2014-08-11T00:00:00Z") }
}, { restaurant_id: 1, name: 1, grades: 1 });

 24
db.restaurant.find({
  "address.coord.1": { $gt: 42, $lte: 52 }
}, { restaurant_id: 1, name: 1, address: 1, coord: 1 });

 25
db.restaurant.find().sort({ name: 1 });

 26
db.restaurant.find().sort({ name: -1 });

 27
db.restaurant.find().sort({ cuisine: 1, borough: -1 });

 28
db.restaurant.find({ "address.street": { $exists: true } });

 29
db.restaurant.find({ "coord": { $type: "double" } });

 30
db.restaurant.find({ "grades.score": { $mod: [7, 0] } }, { restaurant_id: 1, name: 1, grades: 1 });

 31
db.restaurant.find({ name: /mon/ }, { restaurant_id: 1, name: 1, borough: 1, "address.coord": 1, cuisine: 1 });

 32
db.restaurant.find({ name: /^Mad/ }, { restaurant_id: 1, name: 1, borough: 1, "address.coord": 1, cuisine: 1 });

 33
db.restaurant.find({ "grades.score": { $lt: 5 } });

 34
db.restaurant.find({ "grades.score": { $lt: 5 }, borough: "Manhattan" });

 35
db.restaurant.find({ "grades.score": { $lt: 5 }, $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }] });

 36
db.restaurant.find({
  "grades.score": { $lt: 5 },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $ne: "American" }
});

 37
db.restaurant.find({
  "grades.score": { $lt: 5 },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $nin: ["American", "Chinese"] }
});

 38
db.restaurant.find({ "grades.score": { $gt: 5 } });

 39
db.restaurant.find({ "grades.score": { $gt: 5 }, borough: "Manhattan" });

 40
db.restaurant.find({ "grades.score": { $gt: 5 }, $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }] });

 41
db.restaurant.find({
  "grades.score": { $gt: 5 },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $ne: "American" }
});

 42
db.restaurant.find({
  "grades.score": { $gt: 5 },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $nin: ["American", "Chinese"] }
});

 43
db.restaurant.find({ "grades": { $elemMatch: { score: 2 } } });

 44
db.restaurant.find({ "grades": { $elemMatch: { score: 2 } }, borough: "Manhattan" });

 45
db.restaurant.find({ "grades": { $elemMatch: { score: 2 } }, $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }] });

 46
db.restaurant.find({
  "grades": { $elemMatch: { score: 2 } },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $ne: "American" }
});

 47
db.restaurant.find({
  "grades": { $elemMatch: { score: 2 } },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }],
  cuisine: { $nin: ["American", "Chinese"] }
});

 48
db.restaurant.find({
  "grades": { $not: { $elemMatch: { score: { $lte: 5 } } } }
});

 49
db.restaurant.find({
  "grades": { $not: { $elemMatch: { score: { $lte: 5 } } } },
  borough: "Manhattan"
});

 50
db.restaurant.find({
  "grades": { $not: { $elemMatch: { score: { $lte: 5 } } } },
  $or: [{ borough: "Manhattan" }, { borough: "Brooklyn" }]
});
 51
db.restaurant.aggregate([
  { $group: { _id: "$restaurant_id", averageScore: { $avg: "$grades.score" } } }
]);

 52
db.restaurant.aggregate([
  { $group: { _id: "$restaurant_id", highestScore: { $max: "$grades.score" } } }
]);

 53
db.restaurant.aggregate([
  { $group: { _id: "$restaurant_id", lowestScore: { $min: "$grades.score" } } }
]);

 54
db.restaurant.aggregate([
  { $group: { _id: "$borough", restaurantCount: { $sum: 1 } } }
]);

 55
db.restaurant.aggregate([
  { $group: { _id: "$cuisine", restaurantCount: { $sum: 1 } } }
]);

 56
db.restaurant.aggregate([
  { $group: { _id: { borough: "$borough", cuisine: "$cuisine" }, restaurantCount: { $sum: 1 } } }
]);

 57
db.restaurant.aggregate([
  { $match: { "grades.grade": "A" } },
  { $group: { _id: "$cuisine", restaurantCount: { $sum: 1 } } }
]);

 58
db.restaurant.aggregate([
  { $match: { "grades.grade": "A" } },
  { $group: { _id: "$borough", restaurantCount: { $sum: 1 } } }
]);

 59
db.restaurant.aggregate([
  { $match: { "grades.grade": "A" } },
  { $group: { _id: { borough: "$borough", cuisine: "$cuisine" }, restaurantCount: { $sum: 1 } } }
]);

 60
db.restaurant.aggregate([
  { $group: { _id: { $month: "$grades.date" }, restaurantCount: { $sum: 1 } } }
]);

 61
db.restaurant.aggregate([
  { $group: { _id: "$cuisine", averageScore: { $avg: "$grades.score" } } }
]);

 62
db.restaurant.aggregate([
  { $group: { _id: "$cuisine", highestScore: { $max: "$grades.score" } } }
]);

 63
db.restaurant.aggregate([
  { $group: { _id: "$cuisine", lowestScore: { $min: "$grades.score" } } }
]);

 64
db.restaurant.aggregate([
  { $group: { _id: "$borough", averageScore: { $avg: "$grades.score" } } }
]);

 65
db.restaurant.aggregate([
  { $group: { _id: "$borough", highestScore: { $max: "$grades.score" } } }
]);

 66
db.restaurant.aggregate([
  { $group: { _id: "$borough", lowestScore: { $min: "$grades.score" } } }
]);

 67
db.restaurant.find({ "grades.grade": "A", "grades.date": ISODate("specific_date_here") }, { name: 1, address: 1 });

 68
db.restaurant.find({ "grades.grade": { $in: ["B", "C"] }, "grades.date": ISODate("specific_date_here") }, { name: 1, address: 1 });

 69
db.restaurant.find({
  "grades": { $elemMatch: { grade: "A" } },
  "grades": { $elemMatch: { grade: "B" } }
}, { name: 1, address: 1 });

 70
db.restaurant.find({
  "grades": { $elemMatch: { grade: "A" } },
  "grades": { $not: { $elemMatch: { grade: "B" } } }
}, { name: 1, address: 1 });

 71
db.restaurant.find({
  "grades": { $elemMatch: { grade: "A" } },
  "grades": { $not: { $elemMatch: { grade: "C" } } }
}, { name: 1, address: 1, grades: 1 });

 72
db.restaurant.find({
  "grades": { $elemMatch: { grade: "A" } },
  "grades": { $not: { $elemMatch: { grade: "B" } } },
  "grades": { $not: { $elemMatch: { grade: "C" } } }
}, { name: 1, address: 1, grades: 1 });

 73
db.restaurant.find({ name: /coffee/i }, { name: 1, address: 1 });

 74
db.restaurant.find({ "address.zipcode": /^10/ }, { name: 1, address: 1 });

 75
db.restaurant.find({ cuisine: /^B/ }, { name: 1, address: 1, cuisine: 1 });

 76
db.restaurant.find({ cuisine: /y$/ }, { name: 1, address: 1, cuisine: 1 });

 77
db.restaurant.find({ cuisine: /Pizza/i }, { name: 1, address: 1, cuisine: 1 });

 78
db.restaurant.aggregate([
  { $group: { _id: "$restaurant_id", averageScore: { $avg: "$grades.score" } } },
  { $sort: { averageScore: -1 } },
  { $limit: 1 }
]);

 79
db.restaurant.aggregate([
  { $match: { "grades.grade": "A" } },
  { $group: { _id: "$restaurant_id", gradeCount: { $sum: 1 } } },
  { $sort: { gradeCount: -1 } },
  { $limit: 1 }
]);

 80
db.restaurant.aggregate([
  { $match: { "grades.grade": "C" } },
  { $group: { _id: "$cuisine", gradeCount: { $sum: 1 } } },
  { $sort: { gradeCount: -1 } },
  { $limit: 1 }
]);

 81
db.restaurant.aggregate([
  { $match: { "cuisine": "Turkish" } },
  { $group: { _id: "$restaurant_id", averageScore: { $avg: "$grades.score" } } },
  { $sort: { averageScore: -1 } },
  { $limit: 1 }
]);

 82
db.restaurant.aggregate([
  { $group: { _id: "$restaurant_id", totalScore: { $sum: "$grades.score" } } },
  { $sort: { totalScore: -1 } },
  { $limit: 1 }
]);

 83
db.restaurant.find({ cuisine: "Chinese", borough: "Brooklyn" });

 84
db.restaurant.find({}, { name: 1, address: 1 }).sort({ "grades.date": -1 }).limit(1);

 85
db.restaurant.aggregate([
  { $group: { _id: { cuisine: "$cuisine", restaurant_id: "$restaurant_id" }, averageScore: { $avg: "$grades.score" } } },
  { $sort: { "_id.cuisine": 1, averageScore: -1 } },
  { $group: { _id: "$_id.cuisine", topRestaurants: { $push: { restaurant_id: "$_id.restaurant_id", averageScore: "$averageScore" } } } },
  { $project: { _id: 1, topRestaurants: { $slice: ["$topRestaurants", 5] } } }
]);

 86
db.restaurant.aggregate([
  { $match: { "grades.grade": "A" } },
  { $group: { _id: { borough: "$borough", restaurant_id: "$restaurant_id" }, gradeCount: { $sum: 1 } } },
  { $sort: { "_id.borough": 1, gradeCount: -1 } },
  { $group: { _id: "$_id.borough", topRestaurants: { $push: { restaurant_id: "$_id.restaurant_id", gradeCount: "$gradeCount" } } } },
  { $project: { _id: 1, topRestaurants: { $slice: ["$topRestaurants", 5] } } }
]);

 87
db.restaurant.aggregate([
  { $match: { "grades.grade": "A", "grades.score": { $gte: 90 } } },
  { $group: { _id: "$borough", restaurantCount: { $sum: 1 } } },
  { $sort: { restaurantCount: -1 } },
  { $limit: 1 }
]);