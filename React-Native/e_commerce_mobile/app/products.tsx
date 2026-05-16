
import React from 'react';
import { View, Text, FlatList, StyleSheet, StatusBar, SafeAreaView } from 'react-native';
import { router } from 'expo-router';
import ProductCard from '../components/ProductCard';
import { luxuryStyles } from '../styles/luxuryStyles';

const products = [
    {
      id: 1,
      name: 'Aviator Sunglasses',
      price: '$350',
      image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=2940&auto=format&fit=crop',
      description: 'Classic aviator sunglasses with a modern twist. Perfect for any occasion.',
    },
    {
      id: 2,
      name: 'Round Sunglasses',
      price: '$420',
      image: 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=2940&auto=format&fit=crop',
      description: 'Vintage-inspired round sunglasses that make a bold statement.',
    },
    {
      id: 3,
      name: 'Wayfarer Sunglasses',
      price: '$380',
      image: 'https://images.unsplash.com/photo-1600294038569-96b35e905735?q=80&w=2892&auto=format&fit=crop',
      description: 'Iconic wayfarer design for a timeless and sophisticated look.',
    },
    {
      id: 4,
      name: 'Cat-Eye Sunglasses',
      price: '$450',
      image: 'https://images.unsplash.com/photo-1542546068-444943891599?q=80&w=2940&auto=format&fit=crop',
      description: 'Elegant cat-eye sunglasses that exude glamour and confidence.',
    },
];

export default function ProductListing() {
  return (
    <SafeAreaView style={luxuryStyles.container}>
      <StatusBar barStyle="light-content" />
      <View style={styles.header}>
        <Text style={styles.title}>Our Collection</Text>
      </View>
      <FlatList
        data={products}
        renderItem={({ item, index }) => (
          <ProductCard
            product={item}
            index={index}
            onPress={() => router.push(`/product/${item.id}`)}
          />
        )}
        keyExtractor={(item) => item.id.toString()}
        numColumns={2}
        contentContainerStyle={styles.listContainer}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  header: {
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#333',
  },
  title: {
    fontSize: 32,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: '#FFF',
    textAlign: 'center',
  },
  listContainer: {
    padding: 10,
  },
});
