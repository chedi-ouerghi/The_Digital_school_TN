import { useLocalSearchParams, router } from 'expo-router';
import React, { useState } from 'react';
import { luxuryStyles, luxuryColors } from '../../styles/luxuryStyles';
import { LinearGradient } from 'expo-linear-gradient';
import AnimatedText from '../../components/AnimatedText';
import {
  View,
  Text,
  ScrollView,
  Image,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
} from 'react-native';
import { useCart } from '../../context/CartContext';
import { SafeAreaView } from 'react-native-safe-area-context';

const ProductDetail = () => {
  const product = useLocalSearchParams();
  const { addToCart } = useCart();
  const [quantity, setQuantity] = useState(1);

  const handleBack = () => {
    router.back();
  };

  const increaseQuantity = () => {
    setQuantity(prev => prev + 1);
  };

  const decreaseQuantity = () => {
    setQuantity(prev => (prev > 1 ? prev - 1 : 1));
  };

  const handleAddToCart = () => {
    const productData = {
      id: Number(product.id),
      name: String(product.name),
      price: String(product.price),
      image: String(product.image),
      description: String(product.description),
    };
    addToCart(productData, quantity);
    router.push('/cart');
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="dark-content" />
      <ScrollView>
        <View style={styles.header}>
          <TouchableOpacity onPress={handleBack}>
            <Text style={styles.backButton}>{'< Back'}</Text>
          </TouchableOpacity>
        </View>
        <Image source={{ uri: product.image as string }} style={styles.productImage} />
        <View style={styles.detailsContainer}>
          <Text style={styles.productName}>{product.name}</Text>
          <Text style={styles.productPrice}>{product.price}</Text>
          <AnimatedText text={product.description as string} style={styles.productDescription} />
          
          <View style={styles.quantityContainer}>
            <TouchableOpacity onPress={decreaseQuantity} style={styles.quantityButton}>
              <Text style={styles.quantityButtonText}>-</Text>
            </TouchableOpacity>
            <Text style={styles.quantityText}>{quantity}</Text>
            <TouchableOpacity onPress={increaseQuantity} style={styles.quantityButton}>
              <Text style={styles.quantityButtonText}>+</Text>
            </TouchableOpacity>
          </View>

          <TouchableOpacity onPress={handleAddToCart}>
            <LinearGradient
              colors={[luxuryColors.gold, luxuryColors.darkGold]}
              style={styles.addToCartButton}
            >
              <Text style={styles.addToCartButtonText}>Add to Cart</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: luxuryColors.white,
  },
  header: {
    position: 'absolute',
    top: 20,
    left: 20,
    zIndex: 1,
  },
  backButton: {
    fontSize: 18,
    color: luxuryColors.gold,
    backgroundColor: 'rgba(0,0,0,0.5)',
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 20,
  },
  productImage: {
    width: '100%',
    height: 400,
  },
  detailsContainer: {
    padding: 20,
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    backgroundColor: luxuryColors.white,
    marginTop: -20,
  },
  productName: {
    fontSize: 28,
    fontWeight: 'bold',
    color: luxuryColors.black,
    marginBottom: 10,
    fontFamily: 'PlayfairDisplay_700Bold',
  },
  productPrice: {
    fontSize: 24,
    color: luxuryColors.gold,
    marginBottom: 20,
  },
  productDescription: {
    fontSize: 16,
    color: luxuryColors.darkGray,
    lineHeight: 24,
    marginBottom: 30,
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 30,
  },
  quantityButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: luxuryColors.lightGray,
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityButtonText: {
    fontSize: 20,
    color: luxuryColors.black,
  },
  quantityText: {
    fontSize: 20,
    marginHorizontal: 20,
    fontWeight: 'bold',
  },
  addToCartButton: {
    paddingVertical: 15,
    borderRadius: 30,
    alignItems: 'center',
  },
  addToCartButtonText: {
    color: luxuryColors.white,
    fontSize: 18,
    fontWeight: 'bold',
  },
});

export default ProductDetail;
