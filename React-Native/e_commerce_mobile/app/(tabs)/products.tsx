import { router } from 'expo-router';
import ProductCard from '../../components/ProductCard';
import React from 'react';
import { luxuryStyles } from '../../styles/luxuryStyles';
import { View, Text, FlatList, StyleSheet, StatusBar, SafeAreaView } from 'react-native';

const products = [
  {
    id: 1,
    name: 'Aviator Sunglasses',
    price: '$350',
    image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=2940&auto=format&fit=crop',
    description: 'Classic aviator sunglasses with a modern twist. Featuring a lightweight frame and polarized lenses for maximum comfort and protection.'
  },
  {
    id: 2,
    name: 'Round Sunglasses',
    price: '$420',
    image: 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=2940&auto=format&fit=crop',
    description: 'Chic round sunglasses that add a touch of vintage flair to any outfit. Made with high-quality acetate and scratch-resistant lenses.'
  },
  {
    id: 3,
    name: 'Wayfarer Sunglasses',
    price: '$380',
    image: 'https://images.unsplash.com/photo-1600294038569-26b6c1e24588?q=80&w=2824&auto=format&fit=crop',
    description: 'Iconic wayfarer sunglasses that never go out of style. The durable frame and crystal clear lenses make them a timeless accessory.'
  },
  {
    id: 4,
    name: 'Cat-Eye Sunglasses',
    price: '$450',
    image: 'https://images.unsplash.com/photo-1620138546842-183b35a4a142?q=80&w=2960&auto=format&fit=crop',
    description: 'Elegant cat-eye sunglasses for a glamorous and sophisticated look. The bold design is perfect for making a statement.'
  },
];

const ProductListing = () => {
  const handleProductPress = (product) => {
    router.push({ pathname: `/product/${product.id}`, params: { ...product } });
  };

  return (
    <SafeAreaView style={luxuryStyles.container}>
      <StatusBar barStyle="dark-content" />
      <View style={styles.header}>
        <Text style={styles.headerText}>Our Collection</Text>
      </View>
      <FlatList
        data={products}
        renderItem={({ item, index }) => (
          <ProductCard
            product={item}
            index={index}
            onPress={() => handleProductPress(item)}
          />
        )}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContainer}
      />
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  header: {
    padding: 20,
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  headerText: {
    fontSize: 24,
    fontWeight: 'bold',
    fontFamily: 'PlayfairDisplay_700Bold',
  },
  listContainer: {
    paddingHorizontal: 10,
    paddingTop: 20,
  },
});

export default ProductListing;
